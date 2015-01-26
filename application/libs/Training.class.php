<?php

class Training{
    public $xml;
    public $xmlReader;
    public $distance;
    public $calories;
    public $time;
    public $date;
    public $title;
    public $description;
    public $activity;

    public function __construct($xml){/*
        $xml = str_replace('&lt;', '<', $xml);
        $xml = str_replace('&gt;', '>', $xml);*/

        $this->xmlReader = new XMLReader;
        $this->xmlReader->xml($xml);
    }

    public function getDistance(){
        if($this->distance != null)
            return $this->distance;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();
                if ($exp->nodeName == 'DistanceMeters')
                    return $this->distance = ($exp->nodeValue/1000);
            }
        }

        if(empty($this->distance))
            throw new Exception('Plik nie zawiera dystansu.');
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

        if(empty($this->calories))
            throw new Exception('Plik nie zawiera kalorii.');
    }
    /*<Activity Sport="Running">*/
    public function getActivity(){
        if($this->activity != null)
            return $this->activity;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();
                if ($exp->nodeName == 'Activity Sport="Running"')
                    return $this->activity = 2;
                elseif ($exp->nodeName == 'Activity Sport="Biking"')
                    return $this->activity = 1;
                else
                    return $this->activity = 1;
            }
        }

        if(empty($this->activity))
            throw new Exception('Plik nie zawiera aktywnośći.');
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

        if(empty($this->time))
            throw new Exception('Plik nie zawiera czasu.');
    }

    public function getDate(){

        if($this->date != null)
            return $this->date;

        while ($this->xmlReader->read()) {
            if ($this->xmlReader->nodeType == XMLReader::ELEMENT) {
                $exp = $this->xmlReader->expand();

                if ($exp->nodeName == 'Id'){
                    $date = $exp->nodeValue;
                    var_dump($date[10]);
                    if($date[10] == 'T'){
                        $date[10] = ' ';
                        $date = substr($date, 0, -5);
                        // <Id>2014-06-08T07:55:42.000Z</Id>
                        return $this->date = DateTime::createFromFormat("Y-m-d h:i:s", $date)->format('Y-m-d G:i:s');
                    }
                    else{
                        return $this->date = DateTime::createFromFormat('D M j G:i:s T Y', $date)->format('Y-m-d G:i:s');
                    }

                }
            }
        }

        if(empty($this->date))
            throw new Exception('Plik nie zawiera daty.');
    }

    public function getTitle(){
        if($this->title != null)
            return $this->title;

        return $this->title = 'Trening z dnia '.$this->getDate();
    }
    public function getDescription(){
        if($this->description != null)
            return $this->description;

        return $this->description = 'Automatyczny pis treningu';
    }
}
