<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Region;

class RegionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $regions = [
            // Canadian Provinces and Territories
            'Alberta',
            'British Columbia',
            'Manitoba',
            'New Brunswick',
            'Newfoundland and Labrador',
            'Northwest Territories',
            'Nova Scotia',
            'Nunavut',
            'Ontario',
            'Prince Edward Island',
            'Quebec',
            'Saskatchewan',
            'Yukon',
            
            // US States
            'Alabama',
            'Alaska',
            'Arizona',
            'Arkansas',
            'California',
            'Colorado',
            'Connecticut',
            'Delaware',
            'Florida',
            'Georgia',
            'Hawaii',
            'Idaho',
            'Illinois',
            'Indiana',
            'Iowa',
            'Kansas',
            'Kentucky',
            'Louisiana',
            'Maine',
            'Maryland',
            'Massachusetts',
            'Michigan',
            'Minnesota',
            'Mississippi',
            'Missouri',
            'Montana',
            'Nebraska',
            'Nevada',
            'New Hampshire',
            'New Jersey',
            'New Mexico',
            'New York',
            'North Carolina',
            'North Dakota',
            'Ohio',
            'Oklahoma',
            'Oregon',
            'Pennsylvania',
            'Rhode Island',
            'South Carolina',
            'South Dakota',
            'Tennessee',
            'Texas',
            'Utah',
            'Vermont',
            'Virginia',
            'Washington',
            'West Virginia',
            'Wisconsin',
            'Wyoming',
        ];

        foreach ($regions as $regionName) {
            Region::firstOrCreate(
                ['region' => $regionName, 'city' => null],
                ['region' => $regionName, 'city' => null]
            );
        }

        // Major Cities from Canada
        $canadianCities = [
            // Ontario
            ['region' => 'Ontario', 'city' => 'Toronto'],
            ['region' => 'Ontario', 'city' => 'Ottawa'],
            ['region' => 'Ontario', 'city' => 'Hamilton'],
            ['region' => 'Ontario', 'city' => 'London'],
            ['region' => 'Ontario', 'city' => 'Mississauga'],
            ['region' => 'Ontario', 'city' => 'Brampton'],
            ['region' => 'Ontario', 'city' => 'Windsor'],
            ['region' => 'Ontario', 'city' => 'Kitchener'],
            ['region' => 'Ontario', 'city' => 'Waterloo'],
            ['region' => 'Ontario', 'city' => 'Oshawa'],
            ['region' => 'Ontario', 'city' => 'Burlington'],
            ['region' => 'Ontario', 'city' => 'Sudbury'],
            ['region' => 'Ontario', 'city' => 'Kingston'],
            ['region' => 'Ontario', 'city' => 'Thunder Bay'],
            // Quebec
            ['region' => 'Quebec', 'city' => 'Montreal'],
            ['region' => 'Quebec', 'city' => 'Quebec City'],
            ['region' => 'Quebec', 'city' => 'Laval'],
            ['region' => 'Quebec', 'city' => 'Gatineau'],
            ['region' => 'Quebec', 'city' => 'Longueuil'],
            ['region' => 'Quebec', 'city' => 'Sherbrooke'],
            ['region' => 'Quebec', 'city' => 'Saguenay'],
            ['region' => 'Quebec', 'city' => 'Lévis'],
            ['region' => 'Quebec', 'city' => 'Trois-Rivières'],
            // British Columbia
            ['region' => 'British Columbia', 'city' => 'Vancouver'],
            ['region' => 'British Columbia', 'city' => 'Victoria'],
            ['region' => 'British Columbia', 'city' => 'Surrey'],
            ['region' => 'British Columbia', 'city' => 'Burnaby'],
            ['region' => 'British Columbia', 'city' => 'Richmond'],
            ['region' => 'British Columbia', 'city' => 'Kelowna'],
            ['region' => 'British Columbia', 'city' => 'Abbotsford'],
            ['region' => 'British Columbia', 'city' => 'Coquitlam'],
            ['region' => 'British Columbia', 'city' => 'Saanich'],
            // Alberta
            ['region' => 'Alberta', 'city' => 'Calgary'],
            ['region' => 'Alberta', 'city' => 'Edmonton'],
            ['region' => 'Alberta', 'city' => 'Red Deer'],
            ['region' => 'Alberta', 'city' => 'Lethbridge'],
            ['region' => 'Alberta', 'city' => 'St. Albert'],
            ['region' => 'Alberta', 'city' => 'Medicine Hat'],
            // Manitoba
            ['region' => 'Manitoba', 'city' => 'Winnipeg'],
            ['region' => 'Manitoba', 'city' => 'Brandon'],
            // Saskatchewan
            ['region' => 'Saskatchewan', 'city' => 'Saskatoon'],
            ['region' => 'Saskatchewan', 'city' => 'Regina'],
            // Nova Scotia
            ['region' => 'Nova Scotia', 'city' => 'Halifax'],
            // New Brunswick
            ['region' => 'New Brunswick', 'city' => 'Saint John'],
            ['region' => 'New Brunswick', 'city' => 'Moncton'],
            ['region' => 'New Brunswick', 'city' => 'Fredericton'],
            // Newfoundland and Labrador
            ['region' => 'Newfoundland and Labrador', 'city' => 'St. John\'s'],
            // Northwest Territories
            ['region' => 'Northwest Territories', 'city' => 'Yellowknife'],
            // Yukon
            ['region' => 'Yukon', 'city' => 'Whitehorse'],
        ];

        // Major Cities from USA
        $usCities = [
            // Texas
            ['region' => 'Texas', 'city' => 'Houston'],
            ['region' => 'Texas', 'city' => 'Dallas'],
            ['region' => 'Texas', 'city' => 'Austin'],
            ['region' => 'Texas', 'city' => 'San Antonio'],
            ['region' => 'Texas', 'city' => 'Fort Worth'],
            ['region' => 'Texas', 'city' => 'El Paso'],
            ['region' => 'Texas', 'city' => 'Arlington'],
            ['region' => 'Texas', 'city' => 'Corpus Christi'],
            ['region' => 'Texas', 'city' => 'Plano'],
            ['region' => 'Texas', 'city' => 'Laredo'],
            ['region' => 'Texas', 'city' => 'Lubbock'],
            ['region' => 'Texas', 'city' => 'Garland'],
            ['region' => 'Texas', 'city' => 'Irving'],
            ['region' => 'Texas', 'city' => 'Amarillo'],
            // California
            ['region' => 'California', 'city' => 'Los Angeles'],
            ['region' => 'California', 'city' => 'San Diego'],
            ['region' => 'California', 'city' => 'San Jose'],
            ['region' => 'California', 'city' => 'San Francisco'],
            ['region' => 'California', 'city' => 'Fresno'],
            ['region' => 'California', 'city' => 'Sacramento'],
            ['region' => 'California', 'city' => 'Long Beach'],
            ['region' => 'California', 'city' => 'Oakland'],
            ['region' => 'California', 'city' => 'Bakersfield'],
            ['region' => 'California', 'city' => 'Anaheim'],
            ['region' => 'California', 'city' => 'Santa Ana'],
            ['region' => 'California', 'city' => 'Riverside'],
            ['region' => 'California', 'city' => 'Stockton'],
            ['region' => 'California', 'city' => 'Irvine'],
            ['region' => 'California', 'city' => 'Chula Vista'],
            ['region' => 'California', 'city' => 'Fremont'],
            ['region' => 'California', 'city' => 'San Bernardino'],
            ['region' => 'California', 'city' => 'Modesto'],
            ['region' => 'California', 'city' => 'Oxnard'],
            ['region' => 'California', 'city' => 'Fontana'],
            // New York
            ['region' => 'New York', 'city' => 'New York City'],
            ['region' => 'New York', 'city' => 'Buffalo'],
            ['region' => 'New York', 'city' => 'Rochester'],
            ['region' => 'New York', 'city' => 'Yonkers'],
            ['region' => 'New York', 'city' => 'Syracuse'],
            ['region' => 'New York', 'city' => 'Albany'],
            ['region' => 'New York', 'city' => 'New Rochelle'],
            // Florida
            ['region' => 'Florida', 'city' => 'Jacksonville'],
            ['region' => 'Florida', 'city' => 'Miami'],
            ['region' => 'Florida', 'city' => 'Tampa'],
            ['region' => 'Florida', 'city' => 'Orlando'],
            ['region' => 'Florida', 'city' => 'St. Petersburg'],
            ['region' => 'Florida', 'city' => 'Hialeah'],
            ['region' => 'Florida', 'city' => 'Tallahassee'],
            ['region' => 'Florida', 'city' => 'Fort Lauderdale'],
            ['region' => 'Florida', 'city' => 'Port St. Lucie'],
            ['region' => 'Florida', 'city' => 'Cape Coral'],
            ['region' => 'Florida', 'city' => 'Pembroke Pines'],
            // Illinois
            ['region' => 'Illinois', 'city' => 'Chicago'],
            ['region' => 'Illinois', 'city' => 'Aurora'],
            ['region' => 'Illinois', 'city' => 'Naperville'],
            ['region' => 'Illinois', 'city' => 'Joliet'],
            ['region' => 'Illinois', 'city' => 'Rockford'],
            ['region' => 'Illinois', 'city' => 'Elgin'],
            ['region' => 'Illinois', 'city' => 'Peoria'],
            // Pennsylvania
            ['region' => 'Pennsylvania', 'city' => 'Philadelphia'],
            ['region' => 'Pennsylvania', 'city' => 'Pittsburgh'],
            ['region' => 'Pennsylvania', 'city' => 'Allentown'],
            ['region' => 'Pennsylvania', 'city' => 'Erie'],
            ['region' => 'Pennsylvania', 'city' => 'Reading'],
            // Ohio
            ['region' => 'Ohio', 'city' => 'Columbus'],
            ['region' => 'Ohio', 'city' => 'Cleveland'],
            ['region' => 'Ohio', 'city' => 'Cincinnati'],
            ['region' => 'Ohio', 'city' => 'Toledo'],
            ['region' => 'Ohio', 'city' => 'Akron'],
            // Georgia
            ['region' => 'Georgia', 'city' => 'Atlanta'],
            ['region' => 'Georgia', 'city' => 'Augusta'],
            ['region' => 'Georgia', 'city' => 'Columbus'],
            ['region' => 'Georgia', 'city' => 'Savannah'],
            // North Carolina
            ['region' => 'North Carolina', 'city' => 'Charlotte'],
            ['region' => 'North Carolina', 'city' => 'Raleigh'],
            ['region' => 'North Carolina', 'city' => 'Greensboro'],
            ['region' => 'North Carolina', 'city' => 'Durham'],
            ['region' => 'North Carolina', 'city' => 'Winston-Salem'],
            // Michigan
            ['region' => 'Michigan', 'city' => 'Detroit'],
            ['region' => 'Michigan', 'city' => 'Grand Rapids'],
            ['region' => 'Michigan', 'city' => 'Warren'],
            ['region' => 'Michigan', 'city' => 'Sterling Heights'],
            // New Jersey
            ['region' => 'New Jersey', 'city' => 'Newark'],
            ['region' => 'New Jersey', 'city' => 'Jersey City'],
            ['region' => 'New Jersey', 'city' => 'Paterson'],
            ['region' => 'New Jersey', 'city' => 'Elizabeth'],
            // Virginia
            ['region' => 'Virginia', 'city' => 'Virginia Beach'],
            ['region' => 'Virginia', 'city' => 'Norfolk'],
            ['region' => 'Virginia', 'city' => 'Richmond'],
            ['region' => 'Virginia', 'city' => 'Chesapeake'],
            // Washington
            ['region' => 'Washington', 'city' => 'Seattle'],
            ['region' => 'Washington', 'city' => 'Spokane'],
            ['region' => 'Washington', 'city' => 'Tacoma'],
            ['region' => 'Washington', 'city' => 'Vancouver'],
            // Arizona
            ['region' => 'Arizona', 'city' => 'Phoenix'],
            ['region' => 'Arizona', 'city' => 'Tucson'],
            ['region' => 'Arizona', 'city' => 'Mesa'],
            ['region' => 'Arizona', 'city' => 'Chandler'],
            ['region' => 'Arizona', 'city' => 'Scottsdale'],
            ['region' => 'Arizona', 'city' => 'Glendale'],
            // Massachusetts
            ['region' => 'Massachusetts', 'city' => 'Boston'],
            ['region' => 'Massachusetts', 'city' => 'Worcester'],
            ['region' => 'Massachusetts', 'city' => 'Springfield'],
            // Tennessee
            ['region' => 'Tennessee', 'city' => 'Nashville'],
            ['region' => 'Tennessee', 'city' => 'Memphis'],
            ['region' => 'Tennessee', 'city' => 'Knoxville'],
            ['region' => 'Tennessee', 'city' => 'Chattanooga'],
            // Indiana
            ['region' => 'Indiana', 'city' => 'Indianapolis'],
            ['region' => 'Indiana', 'city' => 'Fort Wayne'],
            ['region' => 'Indiana', 'city' => 'Evansville'],
            // Missouri
            ['region' => 'Missouri', 'city' => 'Kansas City'],
            ['region' => 'Missouri', 'city' => 'St. Louis'],
            ['region' => 'Missouri', 'city' => 'Springfield'],
            // Maryland
            ['region' => 'Maryland', 'city' => 'Baltimore'],
            // Wisconsin
            ['region' => 'Wisconsin', 'city' => 'Milwaukee'],
            ['region' => 'Wisconsin', 'city' => 'Madison'],
            // Colorado
            ['region' => 'Colorado', 'city' => 'Denver'],
            ['region' => 'Colorado', 'city' => 'Colorado Springs'],
            ['region' => 'Colorado', 'city' => 'Aurora'],
            // Minnesota
            ['region' => 'Minnesota', 'city' => 'Minneapolis'],
            ['region' => 'Minnesota', 'city' => 'St. Paul'],
            // South Carolina
            ['region' => 'South Carolina', 'city' => 'Charleston'],
            ['region' => 'South Carolina', 'city' => 'Columbia'],
            // Alabama
            ['region' => 'Alabama', 'city' => 'Birmingham'],
            ['region' => 'Alabama', 'city' => 'Montgomery'],
            ['region' => 'Alabama', 'city' => 'Mobile'],
            // Louisiana
            ['region' => 'Louisiana', 'city' => 'New Orleans'],
            ['region' => 'Louisiana', 'city' => 'Baton Rouge'],
            ['region' => 'Louisiana', 'city' => 'Shreveport'],
            // Kentucky
            ['region' => 'Kentucky', 'city' => 'Louisville'],
            ['region' => 'Kentucky', 'city' => 'Lexington'],
            // Oregon
            ['region' => 'Oregon', 'city' => 'Portland'],
            // Oklahoma
            ['region' => 'Oklahoma', 'city' => 'Oklahoma City'],
            ['region' => 'Oklahoma', 'city' => 'Tulsa'],
            // Connecticut
            ['region' => 'Connecticut', 'city' => 'Bridgeport'],
            ['region' => 'Connecticut', 'city' => 'New Haven'],
            // Utah
            ['region' => 'Utah', 'city' => 'Salt Lake City'],
            // Iowa
            ['region' => 'Iowa', 'city' => 'Des Moines'],
            // Nevada
            ['region' => 'Nevada', 'city' => 'Las Vegas'],
            ['region' => 'Nevada', 'city' => 'Henderson'],
            ['region' => 'Nevada', 'city' => 'Reno'],
            // Arkansas
            ['region' => 'Arkansas', 'city' => 'Little Rock'],
            // Mississippi
            ['region' => 'Mississippi', 'city' => 'Jackson'],
            // Kansas
            ['region' => 'Kansas', 'city' => 'Wichita'],
            ['region' => 'Kansas', 'city' => 'Overland Park'],
            // New Mexico
            ['region' => 'New Mexico', 'city' => 'Albuquerque'],
            // Nebraska
            ['region' => 'Nebraska', 'city' => 'Omaha'],
            ['region' => 'Nebraska', 'city' => 'Lincoln'],
            // West Virginia
            ['region' => 'West Virginia', 'city' => 'Charleston'],
            // Idaho
            ['region' => 'Idaho', 'city' => 'Boise'],
            // Hawaii
            ['region' => 'Hawaii', 'city' => 'Honolulu'],
            // New Hampshire
            ['region' => 'New Hampshire', 'city' => 'Manchester'],
            // Maine
            ['region' => 'Maine', 'city' => 'Portland'],
            // Rhode Island
            ['region' => 'Rhode Island', 'city' => 'Providence'],
            // Montana
            ['region' => 'Montana', 'city' => 'Billings'],
            // Delaware
            ['region' => 'Delaware', 'city' => 'Wilmington'],
            // South Dakota
            ['region' => 'South Dakota', 'city' => 'Sioux Falls'],
            // North Dakota
            ['region' => 'North Dakota', 'city' => 'Fargo'],
            // Alaska
            ['region' => 'Alaska', 'city' => 'Anchorage'],
            // Vermont
            ['region' => 'Vermont', 'city' => 'Burlington'],
            // Wyoming
            ['region' => 'Wyoming', 'city' => 'Cheyenne'],
            // District of Columbia
            ['region' => 'Washington', 'city' => 'Washington DC'],
        ];

        $allCities = array_merge($canadianCities, $usCities);

        foreach ($allCities as $cityData) {
            Region::firstOrCreate(
                ['region' => $cityData['region'], 'city' => $cityData['city']],
                $cityData
            );
        }
    }
}
