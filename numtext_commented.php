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
	    	// if we have the number in our list of fundamental numbers,
	        // we simply return its equivalent text form
	    	return $this->textValues[$pos];
	    }
	    else{
	    	if(strlen($number) < 3){
	    		// two digit numbers that are not in our fundamental list

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
		// Check if the number is not occupying the maximum digits 
		// space for its denomination
		if(strlen($number) % 3 != 0){
			// If this is the case , we append a pad of zeroes to
			// the beginning of the number string to achieve that
			$padTo = strlen($number) + ( 3 - (strlen($number) % 3));

			$number = $this->padDigits($number,$padTo);
		}
	    
	    // Maximum digits that numbers in this denomination can have
	    // This is equal to the length of the number string after the
	    // padding.
		$maxDigits = strlen($number);

		// Get the corresponding highest label for numbers occupying that
		// amount of spaces. eg. A 6 digit maximum space gives a thousand  
		// as the highest label
		$name = $this->maxDigitMappings[$maxDigits];

	    // Get the first three digits of the number.This is the portion
	    // whose text form bears the highest denomination label
	    // eg the '055' in '055000' bears the 'thousand' 
	    $firstThreeDigits = substr($number,0,3);

	    // Get the remaining digits
	    $remainingDigits = substr($number,3,strlen($number));

	    // We start building the text form of the number
		$text = $this->getText($firstThreeDigits)." ".$name." ";

	    // Numerical value of the remaining digits
		$rem = (int)$remainingDigits;
	 
		if($rem >= 100){
			// We need a comma as a separator if we are going to append the 
	        // text form of a number greater than 99
			$text .= ", ";
		}
		else{
			if($rem != 0){
				// We append an 'and' if only we anticipate that the
				// remaining digit whose text form we are going to 
				// append is not a 0
				$text .= "and ";
			}
		}

		if($rem != 0){
			// If the rem is not a 0 , then it makes sense to append its text form
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
		// An accumulator for the numerical value
		$numValue = 0;

		if($this->indexOf($numWord , $this->textValues) != -1){
			// If the given text form exists in our list of fundamental
		    // number text forms,we simply return the corresponding
		    // numerical value
			return $this->numbers[$this->indexOf($numWord , $this->textValues)];
		}
	    
	    // An array of the words in the text form of a number
	    $parts = $this->getParts($numWord);

        // A control variable for looping through the parts array
		$i = 0;

		while($i < count($parts)){
			// hold on to the current element
			$curr = $parts[$i];
	
	        // Stores the numerical value of a complete 
			// subunit of the supplied text
			// Examples of complete subunits are 'sixty' , 
			// 'one hundred and two thousand' , 'six'
			$temp;

			if( (($i + 1) < count($parts)) && (key_exists($parts[$i + 1] , $this->powers))){
				// If the next item is a label eg. 'thousand'

			    // Get that label
				$label = $parts[$i + 1];
	            
	            // Get the corresponding power of 10 and initialise
	            // temp to the value obtained by multiplying the numeric
	            // value of the current item by 10 raised to that power
				$temp = $this->numbers[$this->indexOf($curr , $this->textValues)]
				    * pow(10,$this->powers[$label]);

				if(($i + 2) < count($parts)){
					// If there are at least two more items when at current position
					if($label == "hundred" && $parts[$i + 2] == 'and'){
						// If the item at one place from the current position is a
						// 'hundred' followed by an 'and'.
						// we move the index variable to the postion after the 'and'
						$i += 3;
	                    
	                    while($i < count($parts) && !key_exists($parts[$i] , $this->powers)){
	                    	// We try to get all text forms after the 'and' 
	                    	// until we either come across a label or the end of the 
	                    	// list

	                    	// Building up the subunits linked by the 'and' so that
	                    	// a label can be applied to them
	                    	$temp += $this->numbers[$this->indexOf($parts[$i] , $this->textValues)];

                            // We move the index since we are continuing our iteration
	                    	$i += 1;
	                    }
	                    
	                    if( $i < count($parts)){
	                    	// If we terminated from the previous while loop due to
	                    	// coming across a label , we do a multiplication on the 
	                    	// value accumulated in temp so far using the appropriate
	                    	// power of 10 specified by the label
	                    	$temp *= pow(10 , $this->powers[$parts[$i]]);
	                    }

					}

					else if(key_exists($parts[$i + 2] , $this->powers)){
                        // Else if the item at two places from the current position is
						// also a label

						// We do further multiplication
						$temp *= pow(10 , $this->powers[$parts[$i + 2]]);

						$i += 2;
					}
					else{
						// The item at two positions from current item is not dependent
						// on the current item so temp remains as it is
						// However,we increment the index variable to reflect the fact 
						// that we handled one item in advance 

						$i += 1;
					}
				}
				else{
                    // There is no item at two positions from here
					// But we still need to move the index to reflect the fact
					// that we handled an item in advance 
					$i += 1;
				}
			}
			else{
				// Initialise temp with the numeric value of current item
				$temp =  $this->numbers[$this->indexOf($curr , $this->textValues)];

				if(($i + 2) < count($parts)){
					// Since the condition in the outermost if failed,we know
					// that the next item is also a number text
					// 
					// But here, we go further to check whether there is an 
					// item two positions from the current position and whether
					// that item is a label so we do a multiplication
					$temp += $this->numbers[$this->indexOf($parts[$i + 1] , $this->textValues)];
					$temp *= pow(10,$this->powers[$parts[$i + 2]]);

					$i += 2;
				}
			}
	        
	        if(($i + 1) < count($parts) && $parts[$i + 1] == 'and'){
	        	// If after all the checks and processing above, the next item is an
		        // 'and' , we know that this 'and' is not linking subunits but is just 
		        // a separator , so we skip it
				$i += 1;
			}

            // We accumulate the final values of temp for each iteration
			$numValue += $temp;

            // Move to next item after all the skipped and processed items
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
		// Remove all commas and hyphens in the text
		$numWord = str_replace(['-',','], [' ',' '], $numWord);
		
		
		// Split the text in parts based on spacing 
		$tempParts = explode(' ',$numWord);

		$parts = [];
		
		for($i = 0;$i < count($tempParts);$i++){
			if(strlen($tempParts[$i]) != 0){
				// avoiding extraneous spaces
				$parts[] = $tempParts[$i];
			}
		}

		$copy = $parts;

	    $parts = [];

	    for($i = 0;$i < count($copy);$i++){
	    	if(is_numeric($copy[$i])){
	    		// if a part of the number text was given in numeric form
	    		// we convert it to text form and break it into subparts
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