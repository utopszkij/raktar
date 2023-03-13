<?php
/**
* CURD modul kreálása
*/

include 'config.php';
include 'vendor/database/db.php';

class TableProcessor {
	public $tableName = '';
	public $name = '';
	public $tableExists = false;
	public $fields = [];
	
	function __construct(string $name, string $tableName) {
		$this->name = $name;
		$this->tableName = $tableName;
		$q = new \RATWEB\DB\Query('dbverzio');
		$q->setSql('show tables where Tables_in_'.DBNAME.' = "'.$this->tableName.'"');
		$recs = $q->all();
		if (count($recs) > 0) {
			$this->tableExists = true;
			$q = new \RATWEB\DB\Query('dbverzio');
			$q->setSql('show columns from `'.$this->tableName.'`');
			$this->fields = $q->all();
			
		} 
	}
	
	public function emptyRecord(): string {
		if ($this->tableExists) {
			$fields = $this->fields;
			$result = '';
			foreach ($fields as $field) {
				if ($field->Type == 'int') {
					$result .= '        $result->'.$field->Field.' = 0;'."\n";
				} else if ($field->Type == 'number') {
					$result .= '        $result->'.$field->Field.' = 0;'."\n";
				} else if ($field->Type == 'char') {
					$result .= '        $result->'.$field->Field.' = "";'."\n";
				} else if ($field->Type == 'varchar') {
					$result .= '        $result->'.$field->Field.' = "";'."\n";
				} else if ($field->Type == 'text') {
					$result .= '        $result->'.$field->Field.' = "";'."\n";
				} else if ($field->Type == 'date') {
					$result .= '        $result->'.$field->Field.' = "'.data('Y-m-d').'";'."\n";
				} else if ($field->Type == 'datetime') {
					$result .= '        $result->'.$field->Field.' = "'.data('Y-m-d H:i:s').'";'."\n";
				} else if ($field->Type == 'bool') {
					$result .= '        $result->'.$field->Field.' = 0";'."\n";
				} else {
					$result .= '        $result->'.$field->Field.' = "";'."\n";
				}
			}
		} else {
			$result = '
			    $this->id = 0;
			    $this->name = "";
			';
		}
		return $result;
	}
	 
	public function formFields() : string {
		$result = '';
		if ($this->tableExists) {
			$fields = $this->fields;
			foreach ($fields as $field) {
				if ($field->Field == 'id') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="number" name="'.$field->Field.'" v-model="record.'.$field->Field.'" disabled="disabled" />
						</div>
					</div>
					';
				} else if ($field->Type == 'int') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="number" name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required" />
						</div>
					</div>
					';
				} else if ($field->Type == 'number') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="number" name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required" />
						</div>
					</div>
					';
				} else if ($field->Type == 'char') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="text" name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required" />
						</div>
					</div>
					';
				} else if ($field->Type == 'varchar') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="text" name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required" />
						</div>
					</div>
					';
				} else if ($field->Type == 'text') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<textarea cols="60" rows="5" name="'.$field->Field.'" v-html="record.'.$field->Field.'" required="required"></textarea>
						</div>
					</div>
					';
				} else if ($field->Type == 'date') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="date" name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required" />
						</div>
					</div>
					';
				} else if ($field->Type == 'datetime') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="datetime" name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required" />
						</div>
					</div>
					';
				} else if ($field->Type == 'bool') {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<select name="'.$field->Field.'" v-model="record.'.$field->Field.'" required="required">
								<option value="1">{{ lng(\"YES\") }}</option>
								<option value="0">{{ lng(\"NO\") }}</option>
							</select>
						</div>
					</div>
					';
				} else {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<input type="text" name="'.$field->Field.'" v-model="record.'.$field->Field.'"  required="required" />
						</div>
					</div>
					';
				}
			}
		} else {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("ID") }}</label>:
							<input type="number" name="id" v-model="record.id" disabled="disabled" />
						</div>
						<div class="col-12">
							<label>{{ lng("NAME") }}</label>:
							<input type="text" name="name" v-model="record.name" required="required" />
						</div>
					</div>
					';
		}
		return $result;
	} 
	
	public function showFields() : string {
		$result = '';
		if ($this->tableExists) {
			$fields = $this->fields;
			foreach ($fields as $field) {
					$result .= '
					<div class="row">
						<div class="col-12">
							<label>{{ lng("'.strtoupper($field->Field).'") }}</label>:
							<var v-html="record.'.$field->Field.'"></var>
						</div>
					</div>
					';
			}
		} else {
			$result .= '
			<div class="row">
				<div class="col-12">
					<label>{{ lng("ID") }}</label>:
					<var v-html="record.id"></var>
				</div>
			</div>
			<div class="row">
				<div class="col-12">
					<label>{{ lng("NAME") }}</label>:
					<var v-html="record.name"></var>
				</div>
			</div>
			';
		}			
		return $result;
	} 

	public function lngTokens(): string {
		$result = '';
		if ($this->tableExists) {
			$q = new \RATWEB\DB\Query('dbverzio');
			$q->setSql('show columns from `'.$this->tableName.'`');
			$fields = $q->all();
			foreach ($fields as $field) {
				$result .= '  "'.strtoupper($field->Field).'":"'.$field->Field.'",'."\n";
			}
		}	
		return $result;
	}
}


if (count($argv) < 2)  {
	echo '
	CRUD module creator 
	create controller, model, browser viewer, form viewer, update {lng}.js
	required {documentRoot}/config.php
	use:   
	1. create table in other tools.
	2. type in command line:
	cd {documentRoot}
	php tools/createCRUD.php {controllerName} {tableName}
	(if not define tableName then default: {controllerName}+"s")
	';
	exit();
}
$name = strtolower($argv[1]);
if ((count($argv) == 3) | (!file_exists('includes/controllers/demo.php'))) {
	$tableName = strtolower($argv[2]);
} else {
	$tableName = $name.'s';
}

$tp = new TableProcessor($name, $tableName);

// controller
$lines = file(__DIR__.'/demo.php');
$str = implode("",$lines);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$fp = fopen('includes/controllers/'.$name.'.php','w+');
fwrite($fp,$str);
fclose($fp);
echo 'controller created'."\n";

// model
$lines = file(__DIR__.'/demomodel.php');
$str = implode("",$lines);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$str = str_replace('table = "'.$name.';','table = "'.$name.'s";',$str);

$str = str_replace('//emptyRecord',$tp->emptyRecord(),$str);

$fp = fopen('includes/models/'.$name.'model.php','w+');
fwrite($fp,$str);
fclose($fp);
echo 'model created'."\n";

// viewerek
$lines = file(__DIR__.'/demobrowser.html');
$str = implode("",$lines);
$str = str_replace('DEMO',strtoupper($name),$str);
$str = str_replace('DEMOS',strtoupper($name).'S',$str);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);
$fp = fopen('includes/views/'.$name.'browser.html','w+');
fwrite($fp,$str);
fclose($fp);
echo 'browser viewer created'."\n";

$lines = file(__DIR__.'/demoform.html');
$str = implode("",$lines);
$str = str_replace('DEMO',strtoupper($name),$str);
$str = str_replace('DEMOS',strtoupper($name).'S',$str);
$str = str_replace('demo',$name,$str);
$str = str_replace('Demo',ucfirst($name),$str);

$str = str_replace('//formFields',$tp->formFields(),$str);
$str = str_replace('//showFields',$tp->showFields(),$str);
if (count($tp->fields) > 1) {
	$str = str_replace('//focus','document.querySelector("input[name=\"'.$tp->fields[1]->Field.'\"]").focus();',$str);
}
$fp = fopen('includes/views/'.$name.'form.html','w+');
fwrite($fp,$str);
fclose($fp);
echo 'form viewer created'."\n";

// languages
$lines = file('languages/'.LNG.'.js');
$str = implode("",$lines);
$str = str_replace('"END":"Vége"',
'/* '.$name.' */'."\n".
'  "'.strtoupper($name).'":"'.$name.'",'."\n".
'  "'.strtoupper($name.'s').'":"'.$name.'s",'."\n".
$tp->lngTokens().
''."\n".
'    "END":"Vége"',$str);
$fp = fopen('languages/'.LNG.'.js','w+');
fwrite($fp,$str);
fclose($fp);
echo 'languages file updated, check it!'."\n";


exit;
?>

