<?php 
/*
 * Numtext
 *
 * A library for converting numbers from their numeric forms to their English
 * text equivalent and vice versa for numbers in denominations up to 
 * quadrillion. I believe this denomination is enough for it to be useful 
 * to developers.
 *
 * This library is open source according to the MIT License as follows.
 *
 * Copyright (c) 2016 Richman Larry Clifford
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.  
 *
 */


class Numtext
{
	/**
	 * The numbers whose text forms are fundamental in number to text conversion
	 * they are 0 - 20 and then 30 , 40 , 50 ... 90
	 *
	 * @var array
	 */
	protected $numbers;

    /**
     * Corresponding English text forms for the numbers
     *
     * @var array
     */
	protected $textValues;

    /**
     * A mapping of the maximum spaces occupied by numbers  from thousand    
     * onwards and the corresponding label applied to their first three digits
     *
     * @var array
     */
    protected $maxDigitMappings;

    /**
     * a mapping of denominations to the corresponding powers of 10
     * that their first three digits are multiplied by
     *
     * @var array
     */
	protected $powers;



	/**
	 * Create a new instance of Numtext
	 */
	public function __construct()
	{
		$this->numbers = [];

		for($i = 0;$i <= 20;$i++){
		    $this->numbers[] = $i;
	    }

		for($i = 30;$i <= 90;$i += 10){
			$this->numbers[] = $i;
		}

        $this->textValues = ["zero" , "one" , "two" ,"three" , "four" ,"five" ,"six" ,"seven",
          "eight" ,"nine" ,"ten","eleven", "twelve", "thirteen", "fourteen",
          "fifteen", "sixteen", "seventeen", "eighteen", "nineteen", "twenty",
          "thirty", "fourty", "fifty", "sixty", "seventy", "eighty", "ninety" ];

       
        $this->maxDigitMappings = [6 => 'thousand' , 9 => 'million' , 12 => 'billion',
           15 => 'trillion' , 18 => 'quadrillion'];

        $this->powers = ['hundred'=>2 , 'thousand'=>3 , 'million'=>6 , 'billion' => 9,
           'trillion' => 12,'quadrillion' => 15];        

	}

    /**
	 * Returns the  English text equivalent of a number
	 *
	 * @param  string numeric form of number
	 * @return string the English text form
	 */
	protected function getText($number)
	{
		$pos = $this->indexOf((int)$number , $this->numbers);

		if ($pos != -1){
	    	return $this->textValues[$pos];
	    }
	    else{
	    	if(strlen($number) < 3){

	    		$first = (int)$number[0] * 10;
	    		$second = $number[1];

	    		return $this->getText((string)$first)."-".$this->getText($second);

	    	}
	    	else if(strlen($number) == 3){
	    		return $this->handleHundreds($number);
	    	}
	    	else{
	    		return $this->handleBiggerDenoms($number);
	    	}
        }
	}

    /**
	 * Returns the  English text equivalent of a number
	 * in the hundreds denomination
	 *
	 * @param  string numeric form of number
	 * @return string the English text form
	 */
	protected function handleHundreds($number)
	{
		$firstDigit = $number[0];
		$text = "";

		if($firstDigit != '0')
		    $text = $this->getText($firstDigit)." hundred ";

		$rem = (int)$number - (int)$firstDigit * 100;

		if($rem != 0){
            if($firstDigit != '0')
            	$text .= 'and ';
			$text .= $this->getText((string)$rem);
		}

		return $text;
	}

    /**
	 * Returns the  English text equivalent of a number
	 * in a denomination greater than hundred
	 *
	 * @param  string numeric form of number
	 * @return string the English text form
	 */
	protected function handleBiggerDenoms($number)
	{
		if(strlen($number) % 3 != 0){
			$padTo = strlen($number) + ( 3 - (strlen($number) % 3));

			$number = $this->padDigits($number,$padTo);
		}
	    
		$maxDigits = strlen($number);

		$name = $this->maxDigitMappings[$maxDigits];

	    $firstThreeDigits = substr($number,0,3);

	    $remainingDigits = substr($number,3,strlen($number));

		$text = $this->getText($firstThreeDigits)." ".$name." ";

		$rem = (int)$remainingDigits;
	 
		if($rem >= 100){
			$text .= ", ";
		}
		else{
			if($rem != 0){
				$text .= "and ";
			}
		}

		if($rem != 0){
			$text .= $this->getText((string)$rem);
		}

		return $text;
	}


    /**
	 * Returns the numeric value of a number given as text
	 *
	 * @param  string English text form of number
	 * @return int the numeric form
	 */
	public function getNumericValue ($numWord)
	{
		$numValue = 0;

		if($this->indexOf($numWord , $this->textValues) != -1){
			return $this->numbers[$this->indexOf($numWord , $this->textValues)];
		}
	    
	    $parts = $this->getParts($numWord);

		$i = 0;

		while($i < count($parts)){
			$curr = $parts[$i];
	
			$temp;

			if( (($i + 1) < count($parts)) && (key_exists($parts[$i + 1] , $this->powers))){

				$label = $parts[$i + 1];
	            
				$temp = $this->numbers[$this->indexOf($curr , $this->textValues)]
				    * pow(10,$this->powers[$label]);

				if(($i + 2) < count($parts)){
					if($label == "hundred" && $parts[$i + 2] == 'and'){
						$i += 3;
	                    
	                    while($i < count($parts) && !key_exists($parts[$i] , $this->powers)){

	                    	$temp += $this->numbers[$this->indexOf($parts[$i] , $this->textValues)];

	                    	$i += 1;
	                    }
	                    
	                    if( $i < count($parts)){
	                    	$temp *= pow(10 , $this->powers[$parts[$i]]);
	                    }

					}

					else if(key_exists($parts[$i + 2] , $this->powers)){

						$temp *= pow(10 , $this->powers[$parts[$i + 2]]);

						$i += 2;
					}
					else{

						$i += 1;
					}
				}
				else{
					$i += 1;
				}
			}
			else{
				$temp =  $this->numbers[$this->indexOf($curr , $this->textValues)];

				if(($i + 2) < count($parts)){
					$temp += $this->numbers[$this->indexOf($parts[$i + 1] , $this->textValues)];
					$temp *= pow(10,$this->powers[$parts[$i + 2]]);

					$i += 2;
				}
			}
	        
	        if(($i + 1) < count($parts) && $parts[$i + 1] == 'and'){
				$i += 1;
			}

			$numValue += $temp;

			$i += 1;
		}
		return $numValue;
    }

    /**
	 * Returns the index of an element in an array
	 * Implemented this beacuse I couldn't get any function for this
	 * in the manual
	 *
	 * @param  mixed item the item to search for
	 * @param  array collection the array to search
	 * @return int the index of the item in the collection
	 */
	protected function indexOf($item , $collection)
	{
		for($i = 0;$i < count($collection);$i++){
			if($collection[$i] == $item){
				return $i;
			}
		}

		return -1;
	}

    /**
	 * Returns a numeric string literal with a given number of
	 * zeroes appended at the front
	 *
	 * @param  string number original number
	 * @param  int padTo amount of zeroes to append
	 * @return string number string with zeroes appended at the front
	 */
	protected function padDigits($number , $padTo)
	{
		$temp = "";
	    $pad = $padTo - strlen($number);

		for($i = 1; $i <= $pad;$i++){
			$temp .= "0";
		}

		return $temp.$number;
	}

    /**
	 * Returns the words in the text form of a number 
	 *
	 * @param  string the text form
	 * @return array the words in the next form of a number
	 */
	protected function getParts($numWord)
	{
		$numWord = str_replace(['-',','], [' ',' '], $numWord);
		
		
		$tempParts = explode(' ',$numWord);

		$parts = [];
		
		for($i = 0;$i < count($tempParts);$i++){
			if(strlen($tempParts[$i]) != 0){
				$parts[] = $tempParts[$i];
			}
		}

		$copy = $parts;

	    $parts = [];

	    for($i = 0;$i < count($copy);$i++){
	    	if(is_numeric($copy[$i])){
	    		$subParts = $this->getParts($this->getText($copy[$i]));

	    		for($j = 0;$j < count($subParts);$j++){
	    			$parts[] = $subParts[$j];
	    		}
	    	}
	    	else{
	    		$parts[] = $copy[$i];
	    	}
	    }

		return $parts;
	}

    /**
	 * Returns the alternative form of a number given 
	 *
	 * @param  mixed either number in either text form or numeric
	 * @return mixed the alternative form
	 */
	public function convert($number)
	{
		if(is_numeric($number)){
			return $this->getText((string)$number);
		}
		else{
			return $this->getNumericValue($number);
		}
	}
}
?>