<?php

class SmartScrape{
    
   private $html;
   private $resort = false;
   private $sourceList = [];
   private $respList   = [];
   private $error      = null;

   function __construct($url = ''){
       if($url != ''){ $this->open($url); }
   }

   private function refresh(){
    $this->sourceList = [];
    $this->respList   = [];
    $this->error      = false;
    $this->url        = '';
   }

   public function open($url){
    if($url == null){ 
      $error ="no url supplied"; return false; 
    }else{
      $error = null;
    }
    $this->refresh();
       $this->url = $url;
   }

    public function setItem($key,$target,$attr='',$plainText=false){
          $this->sourceList[$key] = ["target"=>$target,"attr"=>$attr,"text"=>$plainText];
    }

    public function e_message(){
      return $this->error;
    }

    public function scrape(){
      return $this->scrapify();
    }
    
    private function scrapify(){

       return $this->pingify($this->url);
    }

    public function results(){
      return $this->respList;
    }

    public function resort($bool = true){
      $this->resort = is_bool($bool)? $bool : false;
    }

    public function getItem($itemname = ":all",$index=null){
        
       $respList = $this->respList;
        
      if($itemname == ":all"){ 
        return ($this->resort == true)? $this->reorder($respList) : $respList;  
      }

      $respList = $this->reorder($respList);

       if(isset($this->sourceList[$itemname])){
        if($index != null){

          if(isset($respList[$itemname][$index])){ return $respList[$itemname][$index]; }
          return false;

        }else{

          if(isset($respList[$itemname])){ return $respList[$itemname]; }
          return false;
               
        }
       }else{
        $this->error = $itemname."was not set";
        return false;
       }
    }

    public function reorder($results){
         
       $arrResp = [];
       
       foreach ($results as $rName => $rResp) {
       
         if(!isset($rResp['elem-list'])){
             $arrResp[$rName] = []; 
             continue; 
         }      
    
         $i = 0;
         foreach ($rResp as $rRespKey => $value) {

           if(is_array($value)){
               
               $j = 0;
            foreach ($value as $inkey => $inval) {    
                $arrResp[$rName][$j][$rRespKey] = $inval;
                $j++;
            }

           }else{
            if(!isset($arrResp[$rName][$rRespKey])) {$arrResp[$rName][":elem"] = $rResp[':elem']; };
           }
           $i++;
         }


       }

       return $arrResp;
    }

    private function pingify($url){
      try{
        $html = str_get_html(file_get_contents($url));
        $this->html = $html;
           $list = $this->sourceList;
           $respList = [];
           
           foreach ($list as $listKey => $listVal) {   
               $target = $listVal['target']; //img
               $attrR  = (array) $listVal['attr']; // src
            $text   = $listVal['text'];
               $targets = []; 
             //do a dom scrape

            $targets[":elem"] = $target; //store target element

               foreach($html->find($target) as $item) {

                 $targets['elem-list'][] = $item." "; //each elements  
                 $targets['elem-text'][] = ($text == true)? trim($item->plaintext) : null;

                 foreach ($attrR as $rkey => $attr) {
                  $targets[$attr][] = ($item->getAttribute($attr))? $item->getAttribute($attr) : ''; 
                 }
              
               }

               $respList[$listKey] = $targets;
           }        
        $this->respList = $respList;
        $html->clear();
        return true;
      }catch(Exception $e){
        $this->error = $e->getMessage();
        return false;
      }
    }

}


/* 
 * @SmartScrape Tool Documentation (This class works with only dom parser. Please install/include plugin before use)
 * 
 * include_once "plugins/simple_html_dom.php"; //include plugin (if plugin does not exist, please download and install to plugin folder)
 *
 * $SmartScrape = new \core\tools\SmartScrape;  //initialize
 *    
 *                @SmartScrape($url) //initialize with a url
 *
 * $SmartScrape->open($url);  //set url
 *
 * $SmartScrape->setItem($itemName,$target,$attributes,getInnerText); //set new name, target, attribute, getInnerText[true]
 *                      @param $itemName   // access name of set (where to store result)
 *                      @param $target     // target Element of webpage
 *                      @param $attributes // one or more(array) attributes of $target element to be fetched
 *                      @param getInnerText // (bool). Allow or Disallow $target element's text inclusion. Default is false
 *
 * $SmartScrape->setItem("images","img",["src","class"],true); //set new name, target, attributes, getInnerText[true]
 * $SmartScrape->setItem("Title","title"); //set new name, target, no attribute, getInnerText[false]
 *
 * $SmartScrape->resort($bool); // reorganize result (optional). Splits results into separate array forms
 *                      @param $bool = true     //reorganize
 *                      @param $bool = false    //disorganize
 *                      @param $bool : default  //true
 *
 * $SmartScrape->scrape(); // scrape url {
 * $SmartScrape->getItem($item);
 *                       @$item : deafult //":all"  //return all results 
 *                       @$item  deafult //":all"  //return all results 
 * $SmartScrape->e_message(); //get error message
 */


/*
  //@SmartScrape Usage Sample

  $SmartScrape = new \core\tools\SmartScrape;

  $SmartScrape->open("http://site.com");  

  $SmartScrape->setItem("links","a","href",true); 
  $SmartScrape->setItem("images","img",["src",'class']);
  $SmartScrape->setItem("Title","title");

  $SmartScrape->resort();

  if($SmartScrape->scrape()){

    var_dump($SmartScrape->getItem());

    console($SmartScrape->getItem()); view in browser console
    console($SmartScrape->getItem("images")); get only the images
  }else{

    print $e->getMessage();

  }
*/



