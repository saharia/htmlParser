<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PHPHtmlParser\Dom;
use App\Industry;
use App\Company;

class ParseController extends Controller
{
    //
    public function parse(Request $request)
    {
    	ini_set('max_execution_time', 0);
    	$dom = new Dom;
			$dom->loadFromUrl('http://www.mycorporateinfo.com');
			$data = $dom->find('.list-group');
			foreach ($data as $group) {
				// echo $group->find('li');
				foreach ($group->find('li') as $item) {
					$href = $item->find('a')->getAttribute('href');
					if(preg_match("/^\/industry\/section/", $href)) {
						$name = $item->find('a')->innerHtml;
						$exists = Industry::where([ 'name' => $name ])->first();
						if(!$exists) {
							$industry = new Industry;
							$industry->name = $name;
							$industry->save();

							$industryId = $industry->id;

							$fullHref = 'http://www.mycorporateinfo.com'.$href;
							$this->_loadNextCmyDetails($industryId, $fullHref, 1);
						}
					}
				}
			}
			echo "finished";die;
			/*$html = $dom->outerHtml;
			echo $html;die;*/
    }

    public function _loadNextCmyDetails($industryId, $href, $page) {
    	$cmyDom = new Dom;
    	if($page > 1) {
				$cmyDom->loadFromUrl($href.'/page/'.$page);
    	} else {
    		$cmyDom->loadFromUrl($href);
    	}
			$cmyData = $cmyDom->find('table')->find('tbody')->find('tr');
			if($cmyData) {
				$cmyData = $cmyDom->find('table')->find('tbody')->find('tr');
				foreach ($cmyData as $key => $cmyDetails) {
					if($key > 0) {
						$cin = $cmyDetails->find('td')[0]->innerHtml;
						$exists = Company::where([ 'cin' => $cin ])->first();
						if(!$exists) {
							$cmyDetailsDom = new Dom;
			    		$cmyDetailsDom->loadFromUrl('http://www.mycorporateinfo.com'. $cmyDetails->find('td')[1]->find('a')->getAttribute('href'));
			    		$cmyDetailsData = $cmyDetailsDom->find('table')[0]->find('tbody');

							$company = new Company;
							$company->industry_id = $industryId;
							$company->cin = $cin;
							$company->company_name = $cmyDetails->find('td')[1]->find('a')->innerHtml;
							$company->class = $cmyDetails->find('td')[2]->innerHtml;
							$company->status = $cmyDetails->find('td')[3]->innerHtml;

							$doi = $cmyDetailsData->find('tr')[3]->find('td')[1]->innerHtml;
							preg_match("/\d{2}\-\d{2}-\d{4}/", $doi, $date);
							$company->date_of_incorporation = Date('Y-m-d', strtotime($date[0]));
							$company->registration_number = $cmyDetailsData->find('tr')[4]->find('td')[1]->innerHtml;
							$company->category = $cmyDetailsData->find('tr')[5]->find('td')[1]->firstChild();
							$company->sub_category = $cmyDetailsData->find('tr')[6]->find('td')[1]->firstChild();
							$company->roc_code = $cmyDetailsData->find('tr')[8]->find('td')[1]->firstChild();
							$company->no_of_members = $cmyDetailsData->find('tr')[9]->find('td')[1]->innerHtml;
							$company->save();
						}
					}
				}
				if(count($cmyData) == 16) {
					//Load next data.
					$this->_loadNextCmyDetails($industryId, $href, $page + 1);
				}
			}
    }
}
