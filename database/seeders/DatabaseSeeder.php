<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Enquiry;
use App\Models\PageContent;
use App\Models\Project;
use App\Models\Property;
use App\Models\Setting;
use App\Models\TeamMember;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Admin user -------------------------------------------------
        User::updateOrCreate(
            ['email' => 'admin@zamzamestate.pk'],
            ['name' => 'Zam Zam Admin', 'password' => Hash::make('admin12345')]
        );

        // ---- Settings -------------------------------------------------
        Setting::updateOrCreate(['id' => 1], [
            'name'          => 'Zam Zam Estate',
            'legal_name'    => 'Zam Zam Estate',
            'tagline'       => 'Trusted Real Estate Opportunities in Multan',
            'phone'         => '+92 309 666 0071',
            'phone_raw'     => '+923096660071',
            'whatsapp'      => '923096660071',
            'address'       => 'Near Khera Chowk, Budhla Road, Southern Bypass, Multan, Punjab, Pakistan',
            'address_short' => 'Khera Chowk, Budhla Road, Multan',
            'office_hours'  => 'Mon – Sat, 10:00 AM – 7:00 PM',
            'facebook'      => null,
            'instagram'     => null,
        ]);

        // ---- Categories -------------------------------------------------
        $cats = [
            ['residential', 'Residential', 'fa-house', 'Plots and homes for family living.'],
            ['commercial', 'Commercial', 'fa-store', 'Shops, boulevards and plaza plots.'],
            ['farmhouse', 'Farmhouse', 'fa-tree', 'Farmhouse plots and open land.'],
            ['investment', 'Investment', 'fa-chart-line', 'Opportunities flagged for investors.'],
        ];
        foreach ($cats as $i => [$slug, $name, $icon, $desc]) {
            Category::updateOrCreate(['slug' => $slug], [
                'name' => $name, 'icon' => $icon, 'description' => $desc, 'sort_order' => $i,
            ]);
        }

        // ---- Projects -------------------------------------------------
        Project::updateOrCreate(['slug' => 'royal-grace-city'], [
            'name' => 'Royal Grace City',
            'location' => 'Southern Bypass, Multan',
            'status' => 'Active',
            'description' => 'Residential and commercial development along the Southern Bypass corridor of Multan.',
            'sort_order' => 0,
        ]);
        Project::updateOrCreate(['slug' => 'zam-zam-orchard'], [
            'name' => 'Zam Zam Orchard',
            'location' => 'Southern Bypass region, Multan',
            'status' => 'Active',
            'description' => 'Farmhouse-oriented land in the Southern Bypass region of Multan.',
            'sort_order' => 1,
        ]);

        // ---- Team -------------------------------------------------
        TeamMember::updateOrCreate(['id' => 1], [
            'name' => 'Zam Zam Estate',
            'role' => 'Property Team',
            'phone' => '+92 309 666 0071',
            'whatsapp' => '923096660071',
            'image' => 'senior_realtor_male',
            'sort_order' => 0,
        ]);

        // ---- Properties -------------------------------------------------
        foreach ($this->properties() as $i => $p) {
            $p['sort_order'] = $i;
            Property::updateOrCreate(['ref' => $p['ref']], $p);
        }

        // ---- Page content -------------------------------------------------
        $pages = [
            'home_hero_heading' => 'Your Trusted Property Partner in Multan',
            'home_hero_subheading' => 'Explore residential, commercial, land and farmhouse property opportunities across Multan with local market guidance and customer-focused real estate services.',
            'home_about_heading' => 'A Multan-Based Real Estate Brand',
            'home_about_paragraph' => 'Zam Zam Estate is a Multan-based real estate business focused on residential, commercial, land and farmhouse property opportunities around the Khera Chowk, Budhla Road and Southern Bypass corridor.',
            'about_overview' => "Zam Zam Estate is a real estate business based in Multan, Punjab, serving buyers and investors looking for residential, commercial, land and farmhouse opportunities in and around the city's emerging development areas.",
            'about_mission' => 'To simplify real estate decisions by providing customers with practical property opportunities, transparent communication and local market guidance.',
            'about_vision' => "To become a trusted real estate name in Multan by building long-term relationships with customers and contributing to the city's evolving residential, commercial and land-development landscape.",
            'privacy_body' => 'This website formats your enquiry into a message you send to Zam Zam Estate through WhatsApp, or you can call the published phone number. Enquiries submitted through the form on this site are also stored so the team can follow up. We do not sell or share your information.',
            'terms_body' => 'Property titles, prices, plot numbers, sizes, categories, images, features and availability shown on this website are indicative and provided for general information only. They may not reflect current inventory. Confirm all details directly with the Zam Zam Estate sales office before making any decision or payment.',
        ];
        foreach ($pages as $key => $value) {
            PageContent::updateOrCreate(['key' => $key], ['value' => $value, 'group' => explode('_', $key)[0]]);
        }

        $this->call(ContentSeeder::class);

        // ---- Sample enquiries -------------------------------------------------
        if (Enquiry::count() === 0) {
            $samples = [
                ['Bilal Ahmad', '+92 300 4412200', 'Residential', 'ZZ-104', 'Interested in the 1 Kanal plot. Please share payment plan.', 'new', '-1 day'],
                ['Sana Riaz', '+92 321 7788990', 'Farmhouse', 'ZZ-201', 'Want to visit Zam Zam Orchard this weekend.', 'new', '-1 day'],
                ['Usman Tariq', '+92 333 1224567', 'Commercial', 'ZZ-105', 'Need boulevard commercial plot details and frontage.', 'contacted', '-2 days'],
                ['Ayesha Khan', '+92 301 9988776', 'Residential', 'ZZ-102', 'Is ZZ-102 still available? Budget around 60 lakh.', 'contacted', '-2 days'],
                ['Hamza Sheikh', '+92 345 5567001', 'Investment', null, 'Looking for investment options under 1 crore.', 'new', '-3 days'],
                ['Faisal Mahmood', '+92 308 3345789', 'Farmhouse', 'ZZ-203', 'Requesting boundaries and access details for 4 Kanal land.', 'closed', '-4 days'],
                ['Nadia Iqbal', '+92 313 2211009', 'Residential', 'ZZ-106', 'Corner plot — please confirm exact location.', 'closed', '-5 days'],
                ['Zeeshan Ali', '+92 302 6677445', 'Commercial', 'ZZ-107', 'Interested in 1 Kanal commercial for a plaza.', 'contacted', '-6 days'],
            ];
            foreach ($samples as [$name, $phone, $interest, $ref, $msg, $status, $ago]) {
                Enquiry::create([
                    'name' => $name, 'phone' => $phone, 'interest' => $interest,
                    'property_ref' => $ref, 'message' => $msg, 'status' => $status,
                    'created_at' => now()->modify($ago), 'updated_at' => now()->modify($ago),
                ]);
            }
        }
    }

    private function properties(): array
    {
        $rgc = ['project_slug' => 'royal-grace-city', 'project_name' => 'Royal Grace City'];
        $orch = ['project_slug' => 'zam-zam-orchard', 'project_name' => 'Zam Zam Orchard'];

        return [
            array_merge($rgc, [
                'ref' => 'ZZ-101', 'title' => '5 Marla Residential Plot', 'sub_type' => 'Residential Plot',
                'category_slug' => 'residential', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Royal Grace City, Southern Bypass, Multan',
                'address' => 'Royal Grace City, Southern Bypass, near Khera Chowk / Budhla Road, Multan',
                'price_pkr' => 4500000, 'price_formatted' => 'PKR 45 Lakh',
                'size' => '5 Marla', 'size_yds' => 125,
                'status' => 'Featured', 'status_class' => 'featured', 'featured' => true,
                'image' => 'gated_community_aerial', 'gallery' => ['gated_community_aerial', 'designer_house_10marla'],
                'description' => [
                    'A 5 Marla residential plot in Royal Grace City, positioned along the Southern Bypass corridor of Multan with access associated with Budhla Road and the Multan-Faisalabad road network.',
                    'Suited to families planning their first home and to buyers looking for an entry-level opportunity in an expanding part of the city. Confirm the current plot number, category and pricing with the sales office.',
                ],
                'features' => ['Planned residential block', 'Wide internal roads', 'Green-belt provisions', 'Street-lighting provisions', 'Sewerage & underground utility provisions', 'Security arrangements', 'Near main boulevard', 'Southern Bypass connectivity'],
            ]),
            array_merge($rgc, [
                'ref' => 'ZZ-102', 'title' => '7 Marla Residential Plot', 'sub_type' => 'Residential Plot',
                'category_slug' => 'residential', 'purpose' => 'For Sale', 'investment' => false,
                'location' => 'Royal Grace City, Southern Bypass, Multan',
                'address' => 'Royal Grace City, Southern Bypass, near Khera Chowk / Budhla Road, Multan',
                'price_pkr' => 6200000, 'price_formatted' => 'PKR 62 Lakh',
                'size' => '7 Marla', 'size_yds' => 175,
                'status' => 'Verified', 'status_class' => 'verified', 'featured' => true,
                'image' => 'designer_house_10marla', 'gallery' => ['designer_house_10marla', 'gated_community_aerial'],
                'description' => [
                    'A 7 Marla residential plot in Royal Grace City offering a slightly larger footprint for a family home while staying within a manageable budget.',
                    'Located within a planned residential block with provisions for roads, green areas, street lighting and utilities. Current category and pricing to be confirmed with the sales team.',
                ],
                'features' => ['Planned residential block', 'Wide internal roads', 'Parks & play-area provisions', 'Street-lighting provisions', 'Water supply & filtration provisions', 'Security arrangements', 'Community facilities', 'Southern Bypass connectivity'],
            ]),
            array_merge($rgc, [
                'ref' => 'ZZ-103', 'title' => '10 Marla Residential Plot', 'sub_type' => 'Residential Plot',
                'category_slug' => 'residential', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Royal Grace City, Southern Bypass, Multan',
                'address' => 'Royal Grace City, Southern Bypass, near Khera Chowk / Budhla Road, Multan',
                'price_pkr' => 8500000, 'price_formatted' => 'PKR 85 Lakh',
                'size' => '10 Marla', 'size_yds' => 250,
                'status' => 'Featured', 'status_class' => 'featured', 'featured' => true,
                'image' => 'gated_community_aerial', 'gallery' => ['gated_community_aerial', 'modern_villa_1kanal'],
                'description' => [
                    'A 10 Marla residential plot in Royal Grace City for buyers who want more space for a larger home or a longer-term hold in a developing corridor of Multan.',
                    'Part of a planned residential area with historically promoted infrastructure and community-oriented facilities. Verify the on-ground status of facilities and current pricing with the sales office.',
                ],
                'features' => ['Larger residential footprint', 'Planned wide roads', 'Green belts & parks provisions', 'Underground utility provisions', 'Security arrangements', 'Near commercial area', 'Long-term investment potential', 'Southern Bypass connectivity'],
            ]),
            array_merge($rgc, [
                'ref' => 'ZZ-104', 'title' => '1 Kanal Residential Plot', 'sub_type' => 'Residential Plot',
                'category_slug' => 'residential', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Royal Grace City, Southern Bypass, Multan',
                'address' => 'Royal Grace City, Southern Bypass, near Khera Chowk / Budhla Road, Multan',
                'price_pkr' => 16000000, 'price_formatted' => 'PKR 1.6 Crore',
                'size' => '1 Kanal', 'size_yds' => 500,
                'status' => 'Verified', 'status_class' => 'verified', 'featured' => true,
                'image' => 'modern_villa_1kanal', 'gallery' => ['modern_villa_1kanal', 'gated_community_aerial', 'designer_house_10marla'],
                'description' => [
                    "A 1 Kanal residential plot in Royal Grace City suited to buyers planning a spacious family residence in Multan's Southern Bypass corridor.",
                    'Positioned within a planned residential sector with access toward major parts of Multan through Budhla Road and surrounding road networks. Confirm current allocation and pricing with the sales office.',
                ],
                'features' => ['Spacious 1 Kanal plot', 'Planned sector layout', 'Wide roads & green belts', 'Underground utility provisions', 'Security arrangements', 'Near parks & community facilities', 'Long-term investment potential', 'Southern Bypass connectivity'],
            ]),
            array_merge($rgc, [
                'ref' => 'ZZ-105', 'title' => '4 Marla Commercial Plot', 'sub_type' => 'Commercial Plot',
                'category_slug' => 'commercial', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Royal Grace City Main Boulevard, Southern Bypass, Multan',
                'address' => 'Main Commercial Boulevard, Royal Grace City, Southern Bypass, Multan',
                'price_pkr' => 18000000, 'price_formatted' => 'PKR 1.8 Crore',
                'size' => '4 Marla', 'size_yds' => 100,
                'status' => 'Hot Deal', 'status_class' => 'hot', 'featured' => true,
                'image' => 'prime_commercial_arcade', 'gallery' => ['prime_commercial_arcade', 'gated_community_aerial'],
                'description' => [
                    'A 4 Marla commercial plot on the main boulevard of Royal Grace City, positioned for retail or a small commercial building in a developing residential-commercial community.',
                    'Commercial potential is tied to the pace of surrounding development and footfall. Confirm the current commercial category, frontage and pricing with the sales office.',
                ],
                'features' => ['Main boulevard frontage', 'Retail / business potential', 'Within a residential catchment', 'Planned commercial area', 'Utility provisions', 'Security arrangements', 'Long-term investment potential', 'Southern Bypass connectivity'],
            ]),
            array_merge($rgc, [
                'ref' => 'ZZ-106', 'title' => '10 Marla Corner Residential Plot', 'sub_type' => 'Corner Plot',
                'category_slug' => 'residential', 'purpose' => 'For Sale', 'investment' => false,
                'location' => 'Royal Grace City, Southern Bypass, Multan',
                'address' => 'Royal Grace City, Southern Bypass, near Khera Chowk / Budhla Road, Multan',
                'price_pkr' => 9500000, 'price_formatted' => 'PKR 95 Lakh',
                'size' => '10 Marla', 'size_yds' => 250,
                'status' => 'Hot Deal', 'status_class' => 'hot', 'featured' => false,
                'image' => 'designer_house_10marla', 'gallery' => ['designer_house_10marla', 'gated_community_aerial'],
                'description' => [
                    'A 10 Marla corner residential plot in Royal Grace City with two open sides, offering extra light, air and design flexibility for a custom home.',
                    'Corner plots are usually limited in number within a block. Confirm availability, exact location and current pricing with the sales team.',
                ],
                'features' => ['Corner plot - two open sides', 'Planned residential block', 'Wide roads', 'Green-belt & park provisions', 'Underground utility provisions', 'Security arrangements', 'Design flexibility', 'Southern Bypass connectivity'],
            ]),
            array_merge($rgc, [
                'ref' => 'ZZ-107', 'title' => '1 Kanal Commercial Plot', 'sub_type' => 'Commercial Plot',
                'category_slug' => 'commercial', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Royal Grace City Main Boulevard, Southern Bypass, Multan',
                'address' => 'Main Commercial Boulevard, Royal Grace City, Southern Bypass, Multan',
                'price_pkr' => 32000000, 'price_formatted' => 'PKR 3.2 Crore',
                'size' => '1 Kanal', 'size_yds' => 500,
                'status' => 'Verified', 'status_class' => 'verified', 'featured' => false,
                'image' => 'prime_commercial_arcade', 'gallery' => ['prime_commercial_arcade', 'gated_community_aerial', 'modern_villa_1kanal'],
                'description' => [
                    'A 1 Kanal commercial plot on the main boulevard of Royal Grace City, suited to a plaza, showroom or mixed-use commercial building.',
                    'Suited to investors and businesses looking for a strategically positioned asset in an expanding corridor. Confirm the current commercial category and pricing with the sales office.',
                ],
                'features' => ['Prime boulevard frontage', 'Plaza / showroom potential', 'High visibility location', 'Planned commercial zone', 'Utility provisions', 'Security arrangements', 'Long-term investment potential', 'Southern Bypass connectivity'],
            ]),
            array_merge($orch, [
                'ref' => 'ZZ-201', 'title' => '1 Kanal Farmhouse Plot', 'sub_type' => 'Farmhouse Plot',
                'category_slug' => 'farmhouse', 'purpose' => 'For Sale', 'investment' => false,
                'location' => 'Zam Zam Orchard, Southern Bypass, Multan',
                'address' => 'Zam Zam Orchard, Southern Bypass region, Multan',
                'price_pkr' => 5500000, 'price_formatted' => 'PKR 55 Lakh',
                'size' => '1 Kanal', 'size_yds' => 500,
                'status' => 'Featured', 'status_class' => 'featured', 'featured' => true,
                'image' => 'golf_community_estate', 'gallery' => ['golf_community_estate', 'hero_mansion'],
                'description' => [
                    'A 1 Kanal farmhouse plot at Zam Zam Orchard, a farmhouse-oriented location within the Southern Bypass region of Multan for buyers seeking green surroundings and a more private lifestyle.',
                    'Suited to weekend living, family recreation or a smaller farmhouse build while staying connected to Multan. Confirm exact plot size, layout and pricing with the project team.',
                ],
                'features' => ['Green, open surroundings', 'Private farmhouse lifestyle', 'Weekend / recreational use', 'Family-oriented setting', 'Accessible from Multan', 'Long-term land ownership', 'Alternative property investment', 'Southern Bypass region'],
            ]),
            array_merge($orch, [
                'ref' => 'ZZ-202', 'title' => '2 Kanal Farmhouse Plot', 'sub_type' => 'Farmhouse Plot',
                'category_slug' => 'farmhouse', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Zam Zam Orchard, Southern Bypass, Multan',
                'address' => 'Zam Zam Orchard, Southern Bypass region, Multan',
                'price_pkr' => 10500000, 'price_formatted' => 'PKR 1.05 Crore',
                'size' => '2 Kanal', 'size_yds' => 1000,
                'status' => 'Verified', 'status_class' => 'verified', 'featured' => true,
                'image' => 'hero_mansion', 'gallery' => ['hero_mansion', 'golf_community_estate', 'modern_villa_1kanal'],
                'description' => [
                    'A 2 Kanal farmhouse plot at Zam Zam Orchard offering more room for a full farmhouse with lawns, orchard planting and open space away from city congestion.',
                    'Positioned for buyers who want a larger private parcel with long-term land-ownership potential. Confirm the exact parcel, surroundings and pricing with the project team.',
                ],
                'features' => ['Larger private parcel', 'Room for farmhouse + lawns', 'Peaceful green surroundings', 'Family recreation space', 'Accessible from Multan', 'Long-term land ownership', 'Potential investment opportunity', 'Southern Bypass region'],
            ]),
            array_merge($orch, [
                'ref' => 'ZZ-203', 'title' => '4 Kanal Farmhouse Land', 'sub_type' => 'Farmhouse Land',
                'category_slug' => 'farmhouse', 'purpose' => 'For Sale', 'investment' => true,
                'location' => 'Zam Zam Orchard, Southern Bypass, Multan',
                'address' => 'Zam Zam Orchard, Southern Bypass region, Multan',
                'price_pkr' => 19000000, 'price_formatted' => 'PKR 1.9 Crore',
                'size' => '4 Kanal', 'size_yds' => 2000,
                'status' => 'Featured', 'status_class' => 'featured', 'featured' => false,
                'image' => 'golf_community_estate', 'gallery' => ['golf_community_estate', 'hero_mansion', 'gated_community_aerial'],
                'description' => [
                    'A 4 Kanal land parcel at Zam Zam Orchard for buyers seeking a substantial private farmhouse estate or a longer-term land holding in the Southern Bypass region of Multan.',
                    'Suited to a large farmhouse, agricultural surroundings or a family recreational estate. Confirm boundaries, access and current pricing directly with the project team.',
                ],
                'features' => ['Substantial private estate', 'Space for large farmhouse', 'Agricultural surroundings', 'Peaceful, low-density setting', 'Accessible from Multan', 'Long-term land ownership', 'Potential investment opportunity', 'Southern Bypass region'],
            ]),
        ];
    }
}
