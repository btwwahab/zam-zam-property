<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\HeroSlide;
use App\Models\PageContent;
use App\Models\Project;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    /** brand name used only for building default copy */
    private string $brand = 'Horizon Properties';

    public function run(): void
    {
        $this->brand = Setting::current()->name ?: $this->brand;
        $b = $this->brand;

        // ---- Hero settings + slides ----------------------------------
        Setting::current()->update([
            'hero_type' => Setting::current()->hero_type ?: 'slider',
            'hero_badge' => 'Multan, Punjab, Pakistan',
            'hero_video' => null,
            'hero_poster' => 'gated_community_aerial',
            'hero_cta_primary_text' => 'Explore Properties',
            'hero_cta_primary_link' => '/properties.html',
            'hero_cta_secondary_text' => 'Contact Us',
            'hero_cta_secondary_link' => '/contact.html',
            'hero_note' => 'Near Khera Chowk • Budhla Road • Southern Bypass • Multan',
        ]);

        if (HeroSlide::count() === 0) {
            foreach (['gated_community_aerial', 'designer_house_10marla', 'hero_mansion'] as $i => $img) {
                HeroSlide::create(['image' => $img, 'sort_order' => $i]);
            }
        }

        // ---- Category / Project images -----------------------------------
        $catImg = [
            'residential' => 'designer_house_10marla',
            'commercial' => 'prime_commercial_arcade',
            'farmhouse' => 'golf_community_estate',
            'investment' => 'gated_community_aerial',
        ];
        foreach ($catImg as $slug => $img) {
            Category::where('slug', $slug)->whereNull('image')->update(['image' => $img]);
        }

        $projImg = [
            'royal-grace-city' => ['gated_community_aerial', 'Residential & Commercial'],
            'horizon-orchard' => ['golf_community_estate', 'Farmhouse & Land'],
            'zam-zam-orchard' => ['golf_community_estate', 'Farmhouse & Land'],
        ];
        foreach ($projImg as $slug => [$img, $tag]) {
            Project::where('slug', $slug)->whereNull('image')->update(['image' => $img, 'tag' => $tag]);
        }

        // ---- Page content --------------------------------------------
        $text = [
            // ---------- HOME ----------
            'home_hero_heading' => 'Your Trusted Property Partner in Multan',
            'home_hero_subheading' => 'Explore residential, commercial, land and farmhouse property opportunities across Multan with local market guidance and customer-focused real estate services.',

            'home_intro_badge' => 'Built Around Your Goals',
            'home_intro_heading' => 'Property Decisions, Made Clearer',
            'home_intro_p1' => 'Finding the right property is about more than location and price. It is about understanding your objective, evaluating the opportunity and making an informed decision.',
            'home_intro_p2' => "$b helps buyers, sellers and investors navigate the Multan real estate market — with practical guidance, strategic locations and long-term relationships at the centre.",
            'home_intro_btn' => 'More About Our Approach',

            'home_categories_badge' => 'Explore By Category',
            'home_categories_heading' => 'Property Categories',

            'home_featured_badge' => 'Handpicked Opportunities',
            'home_featured_heading' => 'Featured Properties',
            'home_featured_sub' => 'A selection of current opportunities across our projects. Prices and availability are indicative — confirm current details with our team.',
            'home_featured_btn' => 'View All Properties',

            'home_projects_badge' => 'Flagship & Associated Projects',
            'home_projects_heading' => 'Featured Projects',

            'home_why_badge' => "Why $b",
            'home_why_heading' => 'Local Knowledge. Customer-Focused Guidance.',

            'home_about_heading' => 'A Multan-Based Real Estate Brand',
            'home_about_paragraph' => "$b is a Multan-based real estate business focused on residential, commercial, land and farmhouse property opportunities around the Khera Chowk, Budhla Road and Southern Bypass corridor.",
            'home_about_p2' => 'Current listings include residential and commercial plots at our flagship developments, alongside farmhouse land opportunities.',
            'home_about_btn' => 'Read More About Us',

            'home_cta_badge' => "Talk to $b",
            'home_cta_heading' => 'Find Your Next Property Opportunity',
            'home_cta_text' => 'Whether you are searching for a residential plot, commercial opportunity, farmhouse land or a strategic investment, our team is ready to help you explore suitable property options in Multan.',

            // ---------- ABOUT ----------
            'about_hero_badge' => 'About Us',
            'about_hero_heading' => "About $b",
            'about_hero_sub' => "A Multan-based real estate brand focused on connecting buyers, investors and families with strategically located property opportunities across Multan's growing development corridors.",

            'about_overview_badge' => 'Company Overview',
            'about_overview_heading' => 'Real Estate Rooted in Multan',
            'about_overview' => "$b is a real estate business based in Multan, Punjab, serving buyers and investors looking for residential, commercial, land and farmhouse opportunities in and around the city's emerging development areas.",
            'about_overview_p2' => "With its presence around the Khera Chowk, Budhla Road and Southern Bypass corridor, $b operates within one of Multan's developing property zones, where residential communities, commercial developments and larger land opportunities continue to attract buyers and investors.",
            'about_overview_p3' => "From residential plots and commercial opportunities to larger land and farmhouse options, $b aims to provide practical property guidance and facilitate the buying and selling process according to each customer's requirements, budget and objectives.",

            'about_story_badge' => 'Our Story',
            'about_story_heading' => "Part of Multan's Real Estate Ecosystem",
            'about_story_p1' => "$b is a Multan-based real estate business working across the city's growing development corridors, with a focus on the Southern Bypass and Khera Chowk area.",
            'about_story_p2' => 'The team works directly with buyers, sellers and investors on residential, commercial and farmhouse opportunities across our projects.',
            'about_story_p3' => 'Today, the objective is straightforward: to make property buying, selling and investment more accessible through better local knowledge, strategically located opportunities and customer-oriented real estate services.',

            'about_mission' => 'To simplify real estate decisions by providing customers with practical property opportunities, transparent communication and local market guidance.',
            'about_mission_p2' => 'We aim to help individuals, families and investors make informed property decisions while connecting them with opportunities that align with their financial goals and lifestyle requirements.',
            'about_vision' => "To become a trusted real estate name in Multan by building long-term relationships with customers and contributing to the city's evolving residential, commercial and land-development landscape.",
            'about_vision_p2' => 'We aspire to create a property experience where buyers can confidently discover opportunities, understand their options and move forward with greater clarity.',

            'about_values_badge' => 'What We Stand For',
            'about_values_heading' => 'Core Values',

            'about_leadership_badge' => 'Leadership',
            'about_leadership_heading' => 'Our Team',
            'about_leadership_p1' => "$b is run by a small, hands-on property team focused on the Multan market, working directly with buyers, sellers and investors on residential, commercial and farmhouse opportunities.",
            'about_leadership_p2' => 'Individual leadership profiles will be published here once finalised by the company.',

            'about_journey_badge' => 'How We Work',
            'about_journey_heading' => 'The Customer Journey',

            'about_cta_heading' => "Talk to $b",
            'about_cta_text' => 'Tell us what you are looking for and our team can help you explore suitable opportunities in Multan.',

            // ---------- CONTACT ----------
            'contact_hero_badge' => 'Speak With Our Property Team',
            'contact_hero_heading' => "Contact $b",
            'contact_hero_sub' => 'Looking for your next property opportunity in Multan? Our team can help you explore available residential, commercial, land and farmhouse opportunities.',

            'contact_form_badge' => 'Send an Inquiry',
            'contact_form_heading' => "Tell Us What You're Looking For",
            'contact_form_note' => 'Fill in the form and our team will get back to you. Fields marked * are required. Your details are saved so we can follow up, and submitting opens WhatsApp with your details pre-filled.',

            'contact_faq_badge' => 'Frequently Asked',
            'contact_faq_heading' => "Questions About $b",

            // ---------- PROJECTS ----------
            'projects_hero_badge' => 'Southern Bypass Corridor, Multan',
            'projects_hero_heading' => 'Projects & Associated Developments',
            'projects_hero_sub' => "$b is associated with residential, commercial and farmhouse-oriented property activity around Multan's Southern Bypass and Khera Chowk corridor.",

            // ---------- PRIVACY / TERMS ----------
            'privacy_body' => "Information we collect

The enquiry forms on this website ask for your name, phone number and, optionally, your email, budget, preferred location and a message. This information is used only to respond to your enquiry.

How your enquiry is handled

Your details are formatted into a message you send to $b on WhatsApp, or you can call the published number. Enquiries submitted through the form are also stored so the team can follow up. We do not sell or share your information.

Third-party services

Pages load fonts, styles and scripts from third-party content delivery networks, and the contact page embeds a Google Map. These providers may receive standard technical request information such as your IP address.

Contact

For any question about this policy or your information, contact $b at ".Setting::current()->phone.".",
            'terms_body' => "Information is indicative

Property titles, prices, plot numbers, sizes, categories, images, features and availability shown on this website are indicative and provided for general information only. They may not reflect current inventory. Confirm all details directly with the $b sales office before making any decision or payment.

No guarantees

Nothing on this website is a guarantee of investment returns, capital appreciation, rental income, approvals or possession timelines. Real estate values and outcomes are subject to market conditions.

Your responsibility

Buyers should independently verify ownership, documentation, approvals and dues, and should inspect a property and its location before committing.

Contact

Questions about these terms? Contact $b at ".Setting::current()->phone.".",
        ];

        $json = [
            'home_intro_cards' => [
                ['icon' => 'fa-magnifying-glass', 'title' => 'Understand', 'text' => 'We start with your requirement, budget and objective — not a listing.'],
                ['icon' => 'fa-scale-balanced', 'title' => 'Evaluate', 'text' => 'Location, development activity, accessibility and long-term potential.'],
                ['icon' => 'fa-circle-check', 'title' => 'Decide', 'text' => 'Move forward with clear information on price, documentation and terms.'],
            ],
            'home_highlights' => [
                ['icon' => 'fa-solid fa-location-dot', 'title' => 'Multan Focused', 'sub' => 'Southern Bypass corridor'],
                ['icon' => 'fa-solid fa-layer-group', 'title' => '4 Categories', 'sub' => 'Residential to farmhouse'],
                ['icon' => 'fa-solid fa-diagram-project', 'title' => '2 Projects', 'sub' => 'Flagship developments'],
                ['icon' => 'fa-brands fa-whatsapp', 'title' => 'Direct Support', 'sub' => 'Call or WhatsApp anytime'],
            ],
            'home_about_checklist' => ['Residential plots', 'Commercial plots', 'Farmhouse land', 'Investment guidance'],
            'home_why_items' => [
                ['icon' => 'fa-map-location-dot', 'title' => 'Strategic Multan Location', 'text' => 'Our operations are associated with the Khera Chowk, Budhla Road and Southern Bypass corridor — an evolving real estate zone of Multan.'],
                ['icon' => 'fa-location-crosshairs', 'title' => 'Local Property Understanding', 'text' => "We focus on understanding Multan's property market, development corridors and residential communities."],
                ['icon' => 'fa-layer-group', 'title' => 'Wide Property Focus', 'text' => 'Residential, commercial, land and farmhouse-oriented opportunities under one roof.'],
                ['icon' => 'fa-user-check', 'title' => 'Customer-Oriented Guidance', 'text' => "We aim to understand each customer's needs before presenting potential property options."],
                ['icon' => 'fa-comments', 'title' => 'Professional Communication', 'text' => 'Clear and direct communication throughout the property process.'],
                ['icon' => 'fa-handshake-angle', 'title' => 'Long-Term Perspective', 'text' => 'Building lasting customer relationships rather than focusing only on one-time transactions.'],
            ],
            'about_values' => [
                ['icon' => 'fa-shield-halved', 'title' => 'Trust', 'text' => 'Real estate decisions require confidence. We aim to maintain professional communication and build long-term customer relationships.'],
                ['icon' => 'fa-eye-low-vision', 'title' => 'Transparency', 'text' => 'Customers should have clear information about opportunities, pricing, location, documentation and transaction requirements.'],
                ['icon' => 'fa-map-location-dot', 'title' => 'Local Expertise', 'text' => 'Our focus on Multan gives us a strong understanding of local development corridors, communities and property opportunities.'],
                ['icon' => 'fa-user-check', 'title' => 'Customer First', 'text' => 'Every property requirement is different. We focus on understanding the customer before recommending an opportunity.'],
                ['icon' => 'fa-handshake-angle', 'title' => 'Long-Term Relationships', 'text' => 'We focus not only on individual transactions but on building relationships with buyers, investors and property owners.'],
                ['icon' => 'fa-seedling', 'title' => 'Growth', 'text' => 'We believe in identifying opportunities connected with the continued growth and development of Multan.'],
            ],
            'about_journey' => [
                ['title' => 'Understand Your Requirement', 'text' => 'We begin by understanding the type of property you want, your preferred location, budget and objective.'],
                ['title' => 'Explore Suitable Options', 'text' => 'We shortlist property opportunities that align with your requirements.'],
                ['title' => 'Review the Property', 'text' => 'Review location, plot details, property condition and surrounding development.'],
                ['title' => 'Discuss Terms', 'text' => 'Pricing, payment arrangements and transaction requirements are discussed transparently.'],
                ['title' => 'Documentation', 'text' => 'Review property documentation and ownership details before making financial commitments.'],
                ['title' => 'Complete the Transaction', 'text' => 'Once verification and agreements are complete, the transaction proceeds per the applicable documentation and terms.'],
            ],
            'contact_cards' => [
                ['icon' => 'fa-brands fa-whatsapp', 'accent' => '#25D366', 'title' => 'WhatsApp / Message', 'text' => 'Share your requirement, budget and preferred area to get current options.', 'btn' => 'Chat on WhatsApp', 'action' => 'whatsapp'],
                ['icon' => 'fa-solid fa-phone', 'accent' => 'var(--primary-gold)', 'title' => 'Call Now', 'text' => 'Speak directly with our team for buying, selling or investment guidance.', 'btn' => 'Call Now', 'action' => 'call'],
                ['icon' => 'fa-solid fa-location-dot', 'accent' => 'var(--navy-dark)', 'title' => 'Visit Our Area', 'text' => 'Come and meet the team at our office.', 'btn' => 'Open in Maps', 'action' => 'map'],
            ],
            'faqs' => [
                ['q' => 'What does {brand} do?', 'a' => '{brand} is a Multan-based real estate business focused on residential, commercial, land and farmhouse property opportunities.'],
                ['q' => 'Where is {brand} located?', 'a' => 'The business address is {address}.'],
                ['q' => 'What type of properties do you deal in?', 'a' => 'Residential plots, commercial property, larger land parcels, farmhouse opportunities and real estate investment opportunities.'],
                ['q' => 'Can I visit a property before buying?', 'a' => 'Yes. We encourage you to inspect the property location and review the relevant documentation before making a purchase decision.'],
                ['q' => 'Are property prices fixed?', 'a' => 'Property prices, availability and payment terms may change according to location, inventory and market conditions. Prices shown on this website are indicative — confirm current pricing directly with the team.'],
            ],
        ];

        foreach ($text as $key => $value) {
            PageContent::updateOrCreate(['key' => $key], ['value' => $value, 'group' => explode('_', $key)[0]]);
        }
        foreach ($json as $key => $value) {
            PageContent::updateOrCreate(['key' => $key], ['value' => json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), 'group' => explode('_', $key)[0]]);
        }
    }
}
