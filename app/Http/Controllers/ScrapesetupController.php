<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScrapeMaster;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Redis;

class ScrapesetupController extends Controller
{
    public function index(Request $request)    {
        
        $token = $request->bearerToken();
        $jsonData = json_encode($request->json()->all());
        $arr_jobs = json_decode($jsonData);
        $total_jobs = count($arr_jobs);
        $slno = 0; 
        $result_array = array();
        for($i=0; $i<$total_jobs; $i++) {

            $job_source_type =  strip_tags(trim($arr_jobs[$i]->job_source_type));
            $job_section_type =  strip_tags(trim($arr_jobs[$i]->job_section_type));
            $job_url =  strip_tags(trim($arr_jobs[$i]->job_url));
            $job_post_date =  strip_tags(trim($arr_jobs[$i]->job_post_date));
            $job_title_tags = $arr_jobs[$i]->html->job_title_tags;
            $job_location_tags = $arr_jobs[$i]->html->job_location_tags;
            $job_company_tags = $arr_jobs[$i]->html->job_company_tags;
            $job_location_type_tags = $arr_jobs[$i]->html->job_location_type_tags;
            $job_salary_tags = $arr_jobs[$i]->html->job_salary_tags;
          
            $slno = $this->getRecordID_redis();
            $html = file_get_contents($job_url);
            $html_file_name = "dice_job".$slno.".txt";
            $myfile = fopen( $html_file_name, "w") or die("Unable to open file!");
            fwrite($myfile, $html);
            fclose($myfile);
            $created_at = date("Y-m-d H:m:s"); 
            $url_scrap_status = 0; 

            $scrap_master_data = array(
                'job_id' => $slno ,
                'job_source_type' => $job_source_type,
                'job_section_type' => $job_section_type,
                'job_url' => $job_url,
                'html_file_name' => $html_file_name,
                'job_title_tags' => $job_title_tags,
                'job_location_tags' => $job_location_tags,
                'job_company_tags' => $job_company_tags,
                'job_location_type_tags' => $job_location_type_tags,
                'job_salary_tags' => $job_salary_tags,
                'job_post_date' => $job_post_date,
                'created_at' => $created_at,
                'url_scrap_status' => $url_scrap_status
            );
    
            $status = $this->addJobMaster_redis($scrap_master_data, $slno); 
            if($status !=  0) {
                $scrape_status = $this->processURLbyKeyID_redis($slno);
                if($scrape_status == "1") {
                    $scrape_status = "Successfully scraped" ;
                } else {
                    $scrape_status = "Not yet scraped" ;
                }
                $rs = array("job_id" => $status , "ScrapeState" => $scrape_status, "status" => "Success");
            } else {
                $rs = array("json_post_ID" => $i , "status" => "Failed");
            }

            array_push($result_array, $rs);

        }

        return response()->json([
            'access_token' => $token,
            'result' => $result_array
        ]);

      
    }


    public function processURLbyKeyID_redis($slno) {
      
       
        $response = $this->getJobMaster($slno);
        if(isset($response->job_id)) {
            
            if(intval($response->url_scrap_status) == 0) {

                $job_id = $response->job_id;
                $job_title_tags = $response->job_title_tags;
                $job_source_type =  $response->job_source_type;
                $job_section_type =  $response->job_section_type;
                $job_url =  $response->job_url;
                $html_file_name =  $response->html_file_name;
                $job_title_tags =  $response->job_title_tags;
                $job_company_tags =  $response->job_company_tags;
                $job_location_tags = $response->job_location_tags;
                $job_location_type_tags =  $response->job_location_type_tags;
                $job_salary_tags =  $response->job_salary_tags;
                $job_post_date =  $response->job_post_date;
                $created_at =  $response->created_at;
                $url_scrap_status =  $response->url_scrap_status;

                if($job_source_type == 'DICE') {

                    $html = file_get_contents($html_file_name);
                    $crawler = new Crawler($html);

                    $job_title_arr = explode("," ,$job_title_tags);
                    $seach_param = '//'.$job_title_arr[0].'[contains(@'.$job_title_arr[1].',"'.$job_title_arr[2].'")]';
                    $job_title = $crawler->filterXPath($seach_param )->text();

                    $job_location_arr = explode("," ,$job_location_tags);
                    $seach_param = '//'.$job_location_arr[0].'[contains(@'.$job_location_arr[1].',"'.$job_location_arr[2].'")]';
                    $job_location = $crawler->filterXPath($seach_param )->text();

                    $job_company_arr = explode("," ,$job_company_tags);
                    $seach_param = '//'.$job_company_arr[0].'[contains(@'.$job_company_arr[1].',"'.$job_company_arr[2].'")]';
                    $job_company = $crawler->filterXPath($seach_param )->text();

                    $job_location_type_arr = explode("," ,$job_location_type_tags);
                    $seach_param = '//'.$job_location_type_arr[0].'[contains(@'.$job_location_type_arr[1].',"'.$job_location_type_arr[2].'")]';
                    $job_location_type = $crawler->filterXPath($seach_param )->children()->eq(1)->text();

                    $job_salary_arr = explode("," ,$job_salary_tags);
                    $seach_param = '//'.$job_salary_arr[0].'[contains(@'.$job_salary_arr[1].',"'.$job_salary_arr[2].'")]';
                    $job_salary = $crawler->filterXPath($seach_param )->children()->eq(2)->text();


                    $scrap_job_detail_data = array(
                        'job_id' => $job_id ,
                        'job_source_type' => $job_source_type,
                        'job_section_type' => $job_section_type,
                        'job_url' => $job_url,
                        'job_title' => $job_title,
                        'job_location' => $job_location,
                        'job_company' => $job_company,
                        'job_location_type' => $job_location_type,
                        'job_salary' => $job_salary,
                        'job_post_date' => $job_post_date,
                        'created_at' => $created_at
                    );

                   $status =  $this->addJobDetail_redis($scrap_job_detail_data, $job_id);
                   if($status >  0) {

                     $url_scrap_status =  1;
                     $scrap_master_data = array(
                        'job_id' => $job_id ,
                        'job_source_type' => $job_source_type,
                        'job_section_type' => $job_section_type,
                        'job_url' => $job_url,
                        'html_file_name' => $html_file_name,
                        'job_title_tags' => $job_title_tags,
                        'job_location_tags' => $job_location_tags,
                        'job_company_tags' => $job_company_tags,
                        'job_location_type_tags' => $job_location_type_tags,
                        'job_salary_tags' => $job_salary_tags,
                        'job_post_date' => $job_post_date,
                        'created_at' => $created_at,
                        'url_scrap_status' => $url_scrap_status
                       );

                        $key = "jobMaster:".$job_id;
                        Redis::del($key);
                        $status = $this->addJobMaster_redis($scrap_master_data, $job_id);

                        $resp = $this->getJobMaster($job_id);
                        if(isset($resp->job_id)) {
                            return $resp->url_scrap_status;
                        }
                   } else {
                        return 0;
                   }    
                   

                } else {
                    return 0;
                }
            } else {
                return 2;
            }
          
        } else {
            return -1;
        }

        
    }





    public function addJobMaster_redis( $scrap_master_data, $slno) {
        Redis::set("jobMaster:".$slno,json_encode($scrap_master_data));
        $response =  $this->getJobMaster($slno);
        if(isset($response->job_id)) {
            return $response->job_id;
        } else {
            return 0;
        }
    }


    public function addJobDetail_redis( $scrap_master_data, $slno) {
        Redis::set("jobDetail:".$slno,json_encode($scrap_master_data));
        $response =  $this->getJobMasterDetail($slno);
        if(isset($response->job_id)) {
            return $response->job_id;
        } else {
            return 0;
        }
    }


    public function deleteJobMaster( $id ) {
        $key = "jobMaster:".$id;
        Redis::del($key);
        return response()->json([
            'key_deleted' => $key,
            'checkRecord' => $this->isJobMasterExist($id)
        ]);
    } 

    public function getJobMaster($slno) {
        $response = Redis::get("jobMaster:".$slno);
        $jobMaster = json_decode($response) ;
        return $jobMaster;
    }


    public function getJobMasterDetail($slno) {
        $response = Redis::get("jobDetail:".$slno);
        $jobDetail = json_decode($response) ;
        return $jobDetail;
    }

    public function isJobMasterExist($slno) {
        $response = $this->getJobMaster($slno);
        if(isset($response->job_id)) {
            return true;
        } else {
            return false;
        }
    }

   

    public function getRecordID_redis() {
        $response = Redis::get("jobIDX:0");
        $jobidx = json_decode($response);

        if(!isset($jobidx->id)) {
            $record_no = 1;
            $this->setRecordID_redis($record_no);
        } else {
            $record_no = intval($jobidx->id) + 1;
            $recordSet = array("id" => $record_no);
            Redis::set("jobIDX:0",json_encode($recordSet));
        }

        return $record_no;
    }

    public function getRecordCurrentID_redis() {
        $response = Redis::get("jobIDX:0");
        $jobidx = json_decode($response);
        return $jobidx->id;
    }

    public function setRecordID_redis($record_no) {
        $recordSet = array("id" => $record_no);
        Redis::set("jobIDX:0",json_encode($recordSet));
    }

    public function resetRecordID() {
         $recordSet = array("id" => 0);
         Redis::set("jobIDX:0",json_encode($recordSet));
    }


    public function getAccessToken_redis() {
        $response = Redis::get("appUser:1");
        $resp = json_decode($response);
        return $resp->accessToken;
    }



    public function getJobDetails($id) {
        
        
        $jobMaster = $this->getJobMaster($id);
        $jobDetails = $this->getJobMasterDetail($id);
        
        if(isset($jobMaster->job_id)) {
            $jobDetails =  array( 
                                  "JobDetails" => $jobMaster, 
                                  "JobScrapeData" => $jobDetails
                                );

            return response()->json([
                $jobDetails
            ]);                    
        } else {
            return response()->json([
                'status' => "Record not found",
            ]);

        }
        
    }


    public function deleteJob($id) {
        $keyJobMaster = "jobMaster:".$id;
        Redis::del($keyJobMaster);
        $keyJobDetail = "jobDetail:".$id;
        Redis::del($keyJobDetail);
        return response()->json([
            'key_deleted' => $keyJobMaster,
            'checkRecord' => $this->isJobMasterExist($id)
        ]);
    }

    public function showArray($arr) {

        echo "<pre>";
        print_r($arr);
        die();

    }
}
