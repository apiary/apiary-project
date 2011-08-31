<?php
class metadata_elements {
	public $current_row = 1, $categories_count, $data_array, $categories;

	function __construct(){
		//to change
		$server_base = variable_get('apiary_research_base_url', 'http://localhost');
		ini_set("auto_detect_line_endings", 1);

		$handle = fopen("$server_base/drupal/modules/apiary_project/Metadata_ApiaryDisplay_ROIs_Rules.csv", "r");
		while ( ($data = fgetcsv($handle, 10000, ";") ) != FALSE )
		{
		$number_of_fields = count($data);
		$number_of_fields++;
			if ($this->current_row == 1)
			{
			//Header line
				for ($c=0; $c < $number_of_fields-1; $c++)
				{
					$header_array[$c] = $data[$c];
				}
				$header_array[$c] = 'value';
			}
			else
			{
			//Data line
				for ($c=0; $c < $number_of_fields-1; $c++)
				{
					$this->data_array[$this->current_row-2][$header_array[$c]] = $data[$c];
				}
				$this->data_array[$this->current_row-2]['value']='';
			}
			$this->current_row++;
		}
		fclose($handle);
		//count no. of categories
		$this->categories_count = 0;
		for($i=0; $i <= $this->current_row-2; $i++ )
		{
			if($this->categories_count == 0){
			$this->categories[$this->categories_count] = $this->data_array[$i]["Apiary Display Category"];
			$this->categories_count++;
			}
			$flag = 0;
			for($j=0; $j <= $this->categories_count; $j++ )
			{
				if($this->categories[$j] == $this->data_array[$i]["Apiary Display Category"])
				{
					$flag=1;
					break;
				}
			}
			if($flag==0){
			$this->categories[$this->categories_count] = $this->data_array[$i]["Apiary Display Category"];
			$this->categories_count++;
			}
		}
	}
	function get_metadata(){
		$form = '';
		for($k=2; $k<$this->categories_count+2; $k++)
		{
			$form[$this->categories[$k-2]] = array(
			'#type' => 'fieldset',
			'#title' => t($this->categories[$k-2]),
			'#weight' => $k,
			'#collapsible' => TRUE,
			'#collapsed' => TRUE,
		);

			for($i=0; $i<$this->current_row-2; $i++)
			{
				if(strcmp($this->data_array[$i]["Apiary Display Category"], "")!=0 && strcmp($this->data_array[$i]["Apiary Display Category"], $this->categories[$k-2])==0){
				$val = db_query("select frequent from {apiary_project} where term='%s'", $this->data_array[$i]["DwC/AP Term Name"]);
				$res = db_fetch_object($val);
				$form[$this->data_array[$i]["Apiary Display Category"]][$this->data_array[$i]["DwC/AP Term Name"]] = array(
				'#type' => 'checkbox',
				'#title' => t($this->data_array[$i]["Apiary Display Label"]),
				'#default_value' => $res->frequent,
				);
				}
			}
		}
		return($form);
	}
}
