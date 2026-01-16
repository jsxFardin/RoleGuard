<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $categories = BlogCategory::all()->keyBy('slug');
        
        // Get or create an admin user for blog posts
        $author = User::first();
        if (!$author) {
            $author = User::create([
                'name' => 'Admin User',
                'email' => 'superadmin@admin.com',
                'password' => bcrypt('password'),
            ]);
        }

        $blogPosts = [
            [
                'title' => 'Complete Guide to Company Registration in Bangladesh',
                'slug' => 'complete-guide-company-registration-bangladesh',
                'excerpt' => 'Learn everything you need to know about registering a company in Bangladesh, including required documents, procedures, and timelines.',
                'content' => '<h2>Introduction</h2><p>Registering a company in Bangladesh can seem like a complex process, but with the right guidance, it can be straightforward. This comprehensive guide will walk you through all the necessary steps.</p><h2>Types of Companies</h2><p>In Bangladesh, you can register several types of companies:</p><ul><li>Private Limited Company</li><li>Public Limited Company</li><li>Branch Office</li><li>Joint Venture</li></ul><h2>Required Documents</h2><p>To register your company, you will need:</p><ul><li>Memorandum of Association</li><li>Articles of Association</li><li>Form I, Form VI, Form IX, Form X, Form XII</li><li>Name clearance certificate</li><li>Bank solvency certificate</li></ul><h2>Registration Process</h2><p>The registration process typically takes 15-30 working days, depending on the type of company and completeness of documents.</p><h2>Conclusion</h2><p>With proper preparation and expert guidance, company registration in Bangladesh can be completed efficiently. Contact us for personalized assistance with your company registration needs.</p>',
                'category_slug' => 'company-registration',
                'meta_title' => 'Complete Guide to Company Registration in Bangladesh | Bangladesh Consultant',
                'meta_description' => 'Complete guide to company registration in Bangladesh. Learn about types of companies, required documents, registration procedures, and timelines.',
                'is_published' => true,
                'published_at' => now()->subDays(5),
            ],
            [
                'title' => 'Understanding Tax and VAT Compliance in Bangladesh',
                'slug' => 'understanding-tax-vat-compliance-bangladesh',
                'excerpt' => 'Navigate the complexities of tax and VAT compliance in Bangladesh with our comprehensive guide for businesses.',
                'content' => '<h2>Tax System Overview</h2><p>Bangladesh has a comprehensive tax system that includes income tax, value-added tax (VAT), and various other taxes. Understanding these requirements is crucial for business compliance.</p><h2>Income Tax</h2><p>Companies operating in Bangladesh are subject to corporate income tax. The rates vary depending on the type of company and industry sector.</p><h2>VAT Registration</h2><p>Businesses with annual turnover exceeding BDT 30 lakhs must register for VAT. The registration process involves submitting various documents and maintaining proper records.</p><h2>Compliance Requirements</h2><p>Regular compliance includes:</p><ul><li>Monthly VAT returns</li><li>Annual tax returns</li><li>Maintaining proper books of accounts</li><li>Tax audit requirements</li></ul><h2>Conclusion</h2><p>Staying compliant with tax and VAT regulations is essential for business success in Bangladesh. Our expert team can help ensure your business meets all requirements.</p>',
                'category_slug' => 'tax-compliance',
                'meta_title' => 'Understanding Tax and VAT Compliance in Bangladesh | Bangladesh Consultant',
                'meta_description' => 'Comprehensive guide to tax and VAT compliance in Bangladesh. Learn about income tax, VAT registration, and compliance requirements.',
                'is_published' => true,
                'published_at' => now()->subDays(10),
            ],
            [
                'title' => 'Latest Business News: Economic Growth and Investment Opportunities',
                'slug' => 'latest-business-news-economic-growth-investment-opportunities',
                'excerpt' => 'Stay updated with the latest business news and investment opportunities in Bangladesh\'s growing economy.',
                'content' => '<h2>Economic Growth Trends</h2><p>Bangladesh continues to show strong economic growth, with GDP growth rates consistently above 6% in recent years. This growth presents numerous opportunities for investors and businesses.</p><h2>Key Sectors</h2><p>Several sectors are experiencing significant growth:</p><ul><li>Manufacturing and Export</li><li>Information Technology</li><li>Financial Services</li><li>Infrastructure Development</li></ul><h2>Investment Incentives</h2><p>The government offers various incentives for foreign and local investors, including tax holidays, duty exemptions, and special economic zones.</p><h2>Market Opportunities</h2><p>The growing middle class and increasing digitalization create new market opportunities across various industries.</p><h2>Conclusion</h2><p>Bangladesh offers exciting opportunities for businesses and investors. Understanding the market dynamics and regulatory environment is key to success.</p>',
                'category_slug' => 'business-news',
                'meta_title' => 'Latest Business News: Economic Growth and Investment Opportunities | Bangladesh Consultant',
                'meta_description' => 'Stay updated with latest business news, economic growth trends, and investment opportunities in Bangladesh.',
                'is_published' => true,
                'published_at' => now()->subDays(3),
            ],
            [
                'title' => 'Legal Updates: Recent Changes in Company Law',
                'slug' => 'legal-updates-recent-changes-company-law',
                'excerpt' => 'Important legal updates regarding recent changes in company law and regulations in Bangladesh.',
                'content' => '<h2>Recent Amendments</h2><p>The Companies Act has undergone several important amendments that affect how companies operate in Bangladesh. These changes impact compliance requirements and corporate governance.</p><h2>Key Changes</h2><p>Some of the significant changes include:</p><ul><li>Updated filing requirements</li><li>Changes in director responsibilities</li><li>New disclosure requirements</li><li>Enhanced corporate governance standards</li></ul><h2>Impact on Businesses</h2><p>These changes require companies to review and update their internal processes and compliance procedures. It\'s important to stay informed about these developments.</p><h2>Compliance Considerations</h2><p>Companies should ensure they understand and comply with all new requirements to avoid penalties and maintain good standing.</p><h2>Conclusion</h2><p>Staying updated with legal changes is crucial for business compliance. Our legal experts can help you navigate these updates and ensure your company remains compliant.</p>',
                'category_slug' => 'legal-updates',
                'meta_title' => 'Legal Updates: Recent Changes in Company Law | Bangladesh Consultant',
                'meta_description' => 'Stay informed about recent legal updates and changes in company law affecting businesses in Bangladesh.',
                'is_published' => true,
                'published_at' => now()->subDays(7),
            ],
            [
                'title' => 'Payroll Management Best Practices for Bangladeshi Companies',
                'slug' => 'payroll-management-best-practices-bangladeshi-companies',
                'excerpt' => 'Discover best practices for effective payroll management in Bangladesh, including compliance and efficiency tips.',
                'content' => '<h2>Payroll Management Overview</h2><p>Effective payroll management is crucial for any business operating in Bangladesh. It involves not just paying employees, but ensuring compliance with labor laws and tax regulations.</p><h2>Key Components</h2><p>A comprehensive payroll system should include:</p><ul><li>Salary calculation and processing</li><li>Tax deductions</li><li>Provident fund contributions</li><li>Gratuity calculations</li><li>Leave management</li></ul><h2>Compliance Requirements</h2><p>Payroll management must comply with:</p><ul><li>Labor Act 2006</li><li>Income Tax Ordinance</li><li>Provident Fund regulations</li><li>Gratuity Fund requirements</li></ul><h2>Best Practices</h2><p>Implementing best practices can help streamline payroll processes and ensure accuracy and compliance.</p><h2>Conclusion</h2><p>Proper payroll management is essential for business success. Our payroll services can help ensure your company meets all requirements efficiently.</p>',
                'category_slug' => 'company-registration',
                'meta_title' => 'Payroll Management Best Practices for Bangladeshi Companies | Bangladesh Consultant',
                'meta_description' => 'Learn best practices for payroll management in Bangladesh, including compliance requirements and efficiency tips.',
                'is_published' => true,
                'published_at' => now()->subDays(2),
            ],
            [
                'title' => 'Accounting Software Solutions for Modern Businesses',
                'slug' => 'accounting-software-solutions-modern-businesses',
                'excerpt' => 'Explore the best accounting software solutions for businesses in Bangladesh and how they can improve efficiency.',
                'content' => '<h2>Importance of Accounting Software</h2><p>Modern businesses require efficient accounting software to manage their financial operations effectively. The right software can streamline processes and improve accuracy.</p><h2>Key Features to Look For</h2><p>When selecting accounting software, consider:</p><ul><li>Ease of use</li><li>Compliance with local regulations</li><li>Integration capabilities</li><li>Reporting features</li><li>Security and data protection</li></ul><h2>Popular Solutions</h2><p>Several accounting software solutions are popular in Bangladesh, each with its own strengths and features suited to different business needs.</p><h2>Implementation</h2><p>Proper implementation is crucial for success. This includes data migration, staff training, and ongoing support.</p><h2>Conclusion</h2><p>Choosing and implementing the right accounting software can significantly improve your business operations. Our team can help you select and implement the best solution for your needs.</p>',
                'category_slug' => 'company-registration',
                'meta_title' => 'Accounting Software Solutions for Modern Businesses | Bangladesh Consultant',
                'meta_description' => 'Explore accounting software solutions for businesses in Bangladesh and learn how to choose and implement the right system.',
                'is_published' => true,
                'published_at' => now()->subDays(1),
            ],
        ];

        foreach ($blogPosts as $postData) {
            $categorySlug = $postData['category_slug'] ?? null;
            unset($postData['category_slug']);

            $category = $categorySlug && isset($categories[$categorySlug]) 
                ? $categories[$categorySlug] 
                : null;

            BlogPost::firstOrCreate(
                ['slug' => $postData['slug']],
                [
                    ...$postData,
                    'author_id' => $author->id,
                    'category_id' => $category?->id,
                    'views_count' => rand(50, 500),
                ]
            );
        }
    }
}
