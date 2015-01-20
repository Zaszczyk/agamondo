<?php

class Training{
    public $xml;
    public $xmlReader;
    public $distance;
    public $calories;
    public $time;
    public $date;
    public $title;

    public function __construct($xml){
        $this->xml = $xml;
        $this->xmlReader = new XMLReader();
    }

    public function getDistance(){
        if($this->distance != null)
            return $this->distance;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();
                if ($exp->nodeName == 'DistanceMeters')
                    return $this->distance = $exp->nodeValue;
            }
        }
    }

    public function getCalories(){
        if($this->calories != null)
            return $this->calories;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();
                if ($exp->nodeName == 'Calories')
                    return $this->calories = $exp->nodeValue;
            }
        }
    }

    public function getTime(){
        if($this->time != null)
            return $this->time;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();
                if ($exp->nodeName == 'TotalTimeSeconds'){
                    $seconds = $exp->nodeValue;
                    $hours = floor($seconds / 3600);
                    $mins = floor(($seconds - ($hours*3600)) / 60);
                    $secs = floor($seconds % 60);

                    return $this->time = $hours.':'.$mins.':'.$secs;
                }
            }
        }
    }

    public function getDate(){
        if($this->date != null)
            return $this->date;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();
                if ($exp->nodeName == 'Id'){
                    $date = $exp->nodeValue;
                    return $this->date = DateTime::createFromFormat('D M j G:i:s T Y', $date)->format('Y-m-d G:i:s');
                }
            }
        }
    }

    public function getTitle(){
        if($this->title != null)
            return $this->tile;

        $this->title = 'Trening z dnia '.$this->getDate();
    }
}
