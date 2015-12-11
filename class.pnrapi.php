<?php

class PNRAPI {
	
	private $ch;	//cURL Handler
	//private $pnr1;	//First 3 numbers of the PNR
	//private $pnr2;	//Last 7 numbers of the PNR
        private $pnrNew;
	private $responseRows;
        private $noOfPassengers;
        private $passengerStatus = Array();
        private $chartStatus;
        private $statusScript = "http://www.indianrail.gov.in/cgi_bin/inet_pnrstat_cgi.cgi";
        private $userAgent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/535.1 (KHTML, like Gecko) Chrome/13.0.782.112 Safari/535.1";
        private $nonPassengerRows = 3;

        function __construct($pnrNumber) {
          	$this->setPNR($pnrNumber);            
                $this->houseKeeping();
            	//Fetching Started
            	$this->setResponseRows();
            	$this->noOfPassengers = @$this->responseRows->length - $this->nonPassengerRows;
            	$this->setStatus();
        }
        
        public function getPassengerStatus(){
        	return $this->passengerStatus;
        }
        
        public function getChartStatus(){
        	return $this->chartStatus;
        }
        
        private function houseKeeping(){
		$this->ch = curl_init();	//Initialize the cURL handler
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,true);    //To give the file fetched back to the handler
		curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION,false);   //To stop redirects
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($this->ch, CURLOPT_USERAGENT, $this->userAgent);	//To mask the robot as a browser
		curl_setopt($this->ch, CURLOPT_POSTFIELDS,"lccp_pnrno1={$this->pnrNew}&submit=Wait+For+PNR+Enquiry%21");	//POST fields
        }
        
        private function setStatus() {
		$this->setPassengerStatus();
		$this->setChartStatus();	
        }
        
        private function setPassengerStatus(){
        	for($i=1;$i<=$this->noOfPassengers;$i++){
            	$this->passengerStatus[$this->getInfoFromRow($i,0)] = Array("BookingStatus" =>$this->getInfoFromRow($i,1),"CurrentStatus" => $this->getInfoFromRow($i,2));
        	}
        }
        
        private function setChartStatus(){
        	$chartStatusCol = 1;
        	$this->chartStatus = $this->getInfoFromRow((@$this->responseRows->length)-2,$chartStatusCol);
        }
        
        private function setPNR($pnrNumber){
        	//$this->pnr1 = substr($pnrNumber,0,3);	//Substring from position 0 of length 3 
        	//$this->pnr2 = substr($pnrNumber,3,7);  //Substring from position 3 of length 7  
        	$this->pnrNew = $pnrNumber;
	}
                
        private function setResponseRows(){
        	$responseHTML = $this->fetchPage(true,$this->statusScript);
            	$responseDOM = new DOMDocument();	 //Create new DOM Object
		$responseDOM->strictErrorChecking=false;
		$responseDOM->recover=true;
            	@$responseDOM->loadHTML($responseHTML);	//Load the HTML into the DOM Object
            	$responseTable = $responseDOM->getElementsByTagName("table")->item(25);	//Parse the DOM Object and get the Centre Table
        	$this->responseRows = @$responseTable->childNodes;
        }
        
        private function getInfoFromRow($rowNumber,$colNumber) {
                return @$this->responseRows->item($rowNumber)->getElementsByTagName('td')->item($colNumber)->textContent;
        }
        
        private function fetchPage($sendPost,$pageURL){
                curl_setopt($this->ch, CURLOPT_POST,$sendPost);	//To send the POST parameters
                curl_setopt($this->ch, CURLOPT_URL,$pageURL);	//To set the page to be fetched
                return curl_exec($this->ch);	//Execute and return the response
        }
        
}
?>
