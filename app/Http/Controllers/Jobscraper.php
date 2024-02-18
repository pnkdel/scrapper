<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\Redis;

class Jobscraper extends Controller {

    public function dice() {
      //$html = file_get_contents("https://www.dice.com/job-detail/d731f254-0212-4082-b855-64e688487111");
       $html = file_get_contents("sample1.txt");
        
      /*
        $myfile = fopen("sample3.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $html);
        fclose($myfile);*/
        $crawler = new Crawler($html);

        $job_title = $crawler->filterXPath('//h1[contains(@data-cy,"jobTitle")]')->text();
        $job_location = $crawler->filterXPath('//li[contains(@data-cy,"location")]')->text();
        $job_company = $crawler->filterXPath('//a[contains(@data-cy,"companyNameLink")]')->text();
        $job_location_type = $crawler->filterXPath('//div[contains(@class,"job-overview_jobDetails")]')
                         ->children()->eq(1)->text();
        $job_salary = $crawler->filterXPath('//div[contains(@class,"job-overview_jobDetails")]')
                         ->children()->eq(2)->text();  
        $job_type = $crawler->filterXPath('//div[contains(@class,"job-overview_jobDetails")]')
                         ->children()->eq(3)->text(); 

                        
        $job_description = $crawler->filterXPath('//div[contains(@data-testid,"jobDescriptionHtml")]')
                           ->children();  

      
                         

         $array_jd =  array();

         for($i=0; $i<count($job_description); $i++) {
             
          if( $job_description->eq($i)->text() !== "" )  {

            //  $text = $job_description->eq($i)->text();
            $text = $job_description->eq($i)->text();
            array_push($array_jd,  $text);
           
            
            
          } 

        }


        echo "<pre>";
        print_r($array_jd);        
                          
                         
        /*                   
        $text = "";
       if( $job_description->eq(5) !== null )  {
          echo  $text;       
       } else {
          $text = $job_description->eq(6)->filter('p')->text();
          echo $text."<br>";
       }
       */
                           /*
        echo  count($job_description);
        $text = $job_description->eq(6)->filter('p')->text();
              echo $text."<br>";

              die();*/
       
       
           die();
        /*
         echo "<pre>";
         print_r($job_description-); */
         die();                 

        echo $job_title."<br>";  
        echo $job_location."<br>";
        echo $job_company."<br>"; 
        echo $job_location_type."<br>";   
        echo $job_salary."<br>";  
        echo $job_type."<br>";  
        echo $job_description."<br>";              

    }
    
    public function getJobDetails() {
       
        $jobSite = "EUROJOBS";

        if($jobSite == "EUROJOBS") {
          $html = file_get_contents("https://eurojobs.com/united-kingdom/job/423879556/php-developer-redditch.html?searchId=1708026840.1065&page=1");
          $crawler = new Crawler($html);
        
        $job_title = $crawler->filter('h2')->text();
        $job_location = $crawler->filter('div.narrow-col')->eq(0)->filter('div.displayFieldBlock')->eq(0)->text();
        $job_category = $crawler->filter('div.narrow-col')->eq(0)->filter('div.displayFieldBlock')->eq(1)->text();
        $job_salary = $crawler->filter('div.narrow-col')->eq(0)->filter('div.displayFieldBlock')->eq(2)->text();
        $job_permit_type = $crawler->filter('div.narrow-col')->eq(0)->filter('div.displayFieldBlock')->eq(3)->text();
        
    
        $job_desc = $crawler->filter('fieldset#col-wide')->children()
                            ->filter('div.displayFieldBlock')->children()
                            ->filter('div.displayField')->children()->eq(3)->filter('p')->text();
      
       
        echo $job_title; 
        echo "<br>";
        echo $job_location;
        echo "<br>";
        echo $job_category;
        echo "<br>";
        echo $job_salary;
        echo "<br>";
        echo $job_permit_type;
        echo "<br>";
        echo $job_desc;
    
       die();
        $job_post = array(
                            "job_title" => "PROJECT MANAGER",
                            "job_location" => $job_location,
                            "job_category" => $job_category,
                            "job_salary" => $job_salary,
                            "job_permit_type" => $job_permit_type,
                            "job_desc" => $job_desc,

                         );

        $slno = 3; 
        //Redis::set("jobs:".$slno,json_encode($job_post));

        $response = Redis::get("jobs:".$slno);
        $jobpost = json_decode($response) ;
        print_r($jobpost->job_title);
           
        }
    }


    
}
