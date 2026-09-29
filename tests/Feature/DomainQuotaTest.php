<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\User;
use App\Services\DomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DomainQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_extracts_root_domain_and_tld_correctly(): void
    {
        $service = new DomainService;

        // Indonesia TLDs
        $res1 = $service->extractRootDomain('https://sub.blog.targetsite.co.id/artikel/123');
        $this->assertEquals('targetsite.co.id', $res1['root_domain']);
        $this->assertEquals('.co.id', $res1['tld']);

        $res2 = $service->extractRootDomain('http://kompasiana.web.id/post-seo/');
        $this->assertEquals('kompasiana.web.id', $res2['root_domain']);
        $this->assertEquals('.web.id', $res2['tld']);

        $res3 = $service->extractRootDomain('https://sman1.sch.id/forum/');
        $this->assertEquals('sman1.sch.id', $res3['root_domain']);
        $this->assertEquals('.sch.id', $res3['tld']);

        // Global TLDs
        $res4 = $service->extractRootDomain('https://tech-portal.co.uk/blog');
        $this->assertEquals('tech-portal.co.uk', $res4['root_domain']);
        $this->assertEquals('.co.uk', $res4['tld']);

        $res5 = $service->extractRootDomain('https://digitalagency.com.au/');
        $this->assertEquals('digitalagency.com.au', $res5['root_domain']);
        $this->assertEquals('.com.au', $res5['tld']);

        // Global Single TLDs & new gTLDs
        $res6 = $service->extractRootDomain('https://website-biasa.com/page.html');
        $this->assertEquals('website-biasa.com', $res6['root_domain']);
        $this->assertEquals('.com', $res6['tld']);

        $res7 = $service->extractRootDomain('https://startup.ai/landing');
        $this->assertEquals('startup.ai', $res7['root_domain']);
        $this->assertEquals('.ai', $res7['tld']);

        $res8 = $service->extractRootDomain('https://sub.agency.xyz/portfolio');
        $this->assertEquals('agency.xyz', $res8['root_domain']);
        $this->assertEquals('.xyz', $res8['tld']);

        $res9 = $service->extractRootDomain('https://berlin-news.de/index.php');
        $this->assertEquals('berlin-news.de', $res9['root_domain']);
        $this->assertEquals('.de', $res9['tld']);

        // Worldwide multi-level country code domains (ccSLDs)
        // Africa (Nigeria, South Africa)
        $res10 = $service->extractRootDomain('https://lagos-daily.com.ng/article/view');
        $this->assertEquals('lagos-daily.com.ng', $res10['root_domain']);
        $this->assertEquals('.com.ng', $res10['tld']);

        $res11 = $service->extractRootDomain('https://johannesburg.co.za/post/1');
        $this->assertEquals('johannesburg.co.za', $res11['root_domain']);
        $this->assertEquals('.co.za', $res11['tld']);

        // South America (Brazil, Argentina)
        $res12 = $service->extractRootDomain('https://portal-sao-paulo.com.br/noticias');
        $this->assertEquals('portal-sao-paulo.com.br', $res12['root_domain']);
        $this->assertEquals('.com.br', $res12['tld']);

        $res13 = $service->extractRootDomain('https://diario.com.ar/nota-123');
        $this->assertEquals('diario.com.ar', $res13['root_domain']);
        $this->assertEquals('.com.ar', $res13['tld']);

        // Asia (Japan, Singapore, Pakistan, India)
        $res14 = $service->extractRootDomain('https://tokyo-corp.co.jp/press');
        $this->assertEquals('tokyo-corp.co.jp', $res14['root_domain']);
        $this->assertEquals('.co.jp', $res14['tld']);

        $res15 = $service->extractRootDomain('https://business.com.sg/market');
        $this->assertEquals('business.com.sg', $res15['root_domain']);
        $this->assertEquals('.com.sg', $res15['tld']);

        $res16 = $service->extractRootDomain('https://karachi-times.com.pk/editorial');
        $this->assertEquals('karachi-times.com.pk', $res16['root_domain']);
        $this->assertEquals('.com.pk', $res16['tld']);
    }

    public function test_enforces_maximum_5_urls_per_domain(): void
    {
        $service = new DomainService;
        $user = User::factory()->create([
            'role' => 'worker',
            'default_rate' => 700,
        ]);

        $baseUrl = 'https://beritaviral.co.id/post-';

        // Submit 5 valid URLs under beritaviral.co.id
        for ($i = 1; $i <= 5; $i++) {
            $submission = $service->recordSubmission(
                $user,
                $baseUrl.$i,
                'screenshots/test_'.$i.'.webp',
                'approved_live'
            );
            $this->assertNotNull($submission);
        }

        // Verify domain status is locked and count is 5
        $domain = Domain::where('root_domain', 'beritaviral.co.id')->first();
        $this->assertNotNull($domain);
        $this->assertEquals(5, $domain->url_count);
        $this->assertTrue($domain->is_locked);

        // 6th submission must fail with ValidationException
        $this->expectException(ValidationException::class);
        $service->recordSubmission(
            $user,
            $baseUrl.'6',
            'screenshots/test_6.webp',
            'approved_live'
        );
    }

    public function test_reset_domain_clears_quota_and_unlocks(): void
    {
        $service = new DomainService;
        $domain = Domain::create([
            'root_domain' => 'portalbisnis.id',
            'tld' => '.id',
            'url_count' => 5,
            'max_limit' => 5,
            'is_locked' => true,
        ]);

        $this->assertTrue($domain->is_locked);
        $this->assertEquals(5, $domain->url_count);

        $service->resetDomain($domain);

        $domain->refresh();
        $this->assertFalse($domain->is_locked);
        $this->assertEquals(0, $domain->url_count);
        $this->assertNotNull($domain->last_reset_at);
    }
}
