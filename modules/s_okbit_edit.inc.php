<?php
/*
* @version 0.1 (wizard)
*/
  
  if ($this->owner->name == 'panel') {
	$out['CONTROLPANEL'] = 1;
}



$table_name = 'scene_okbit';

$rec = SQLSelectOne("SELECT * FROM $table_name WHERE ID='$id'");
	
if ($rec['ID']) {
	$Record = SQLSelectOne("SELECT * FROM scenes WHERE ID='".$rec['SCENES_ID']."'");
	
}


	$out['UPDATED'] = $rec['UPDATED'];

if ($this->mode == 'update') {

	$this->getConfig();
	$ok = 1;
	
	if ($this->tab == '') {

		global $title;
		$Record['TITLE'] = $title;
		if ($Record['TITLE'] == '') {
			$out['ERR_TITLE'] = 1;
			$ok = 0;
		}

		global $priority;
		$Record['PRIORITY'] = $priority;
		$rec['PRIORITY'] = $Record['PRIORITY'];
		
		global $templates;
		$rec['TEMPLATE'] = $templates;
		if ($rec['TEMPLATE'] == '') {
			$out['ERR_TYPE_S'] = 1;
			$ok = 0;
		}
		
		global $template_css;
		$rec['TEMPLATE_CSS'] = $template_css;
		if ($rec['TEMPLATE_CSS'] == '') {
			$out['ERR_TEMPLATE_CSS'] = 1;
			$ok = 0;
		}
		
		
				
	}

	if ($ok) {
		if ($rec['ID']) {
			
			DebMes("ID - ".$Record['ID'].' Prioriti - '. $Record['PRIORITY'] , 'scene_okbit');
			SQLUpdate('scenes', $Record);
			SQLUpdate($table_name, $rec);
			
		} else {
			
			$rec['SCENES_ID']=SQLInsert('scenes', $Record);				
			$rec['ID'] = SQLInsert($table_name, $rec);	
			

		//создаем в базе данных позиции длясцены, считаниые из xml файла	
			$xml = simplexml_load_file('./templates/scene_okbit/sc_templates/'.$rec['TEMPLATE'].'/templateDetails.xml');
			foreach ($xml as $position) {
				if($position->namePosition){
						$rec_element = Array();
						$rec_element['SCENE_ID'] = $rec['SCENES_ID'];
						$rec_element['TITLE'] = $position->namePosition;
						$rec_element['TYPE'] = 'html';
						$rec_element['TOP'] = 0;
						$rec_element['LEFT'] = 0;	
						$rec_element['PRIORITY'] = $position->priority;
						$rec_element['ID'] = SQLInsert('elements', $rec_element);
							
						$elm_states['ELEMENT_ID'] = $rec_element['ID'];
						$elm_states['TITLE'] = 'default';
						$elm_states['HTML'] = '';						
						SQLInsert('elm_states', $elm_states);
				}
			}
		}
		
		$out['OK'] = 1;
		

		
	} else {
		$out['ERR'] = 1;
	}
}


if ($this->tab == 'data') {



}


	if ($rec['ID']) {
		$new_rec = SQLSelectOne("SELECT * FROM scenes WHERE ID='".$rec['SCENES_ID']."'");

		$rec['TITLE'] = $new_rec['TITLE'];
		$rec['PRIORITY'] = $new_rec['PRIORITY'];
	}else{
		$rec['TITLE'] = $title;
	}


$filelist = array();

//Сканировнание папки со стилями оформления для вывода списка тем в выподающий список
	
	if ($handle = opendir('./templates/scene_okbit/sc_templates')) {

		while (false !== ($file = readdir($handle))) { 
			if ($file != "." && $file != "..") {
				$filelist[] = $file;
			}
		}
		closedir($handle); 
	}

	$dropdown = $filelist;



	$total = count($dropdown);
	$text_html = '';

	for($i = 0; $i < $total; $i++) { 
		if ($rec['TEMPLATE'] == $dropdown[$i]) $text_html = $text_html . '<option value='.$dropdown[$i].' selected>'.$dropdown[$i].'</option>';
		else $text_html = $text_html.'<option value='.$dropdown[$i].' >' .$dropdown[$i].'</option>';
	} 
	
	
	if ($rec['TEMPLATE']) {	
		$xml = simplexml_load_file('./templates/scene_okbit/sc_templates/'.$rec['TEMPLATE'].'/templateDetails.xml');
		foreach ($xml as $css) {		
			$temp_rec = $css->name;
			if($temp_rec){
				if ($rec['TEMPLATE_CSS'] == $temp_rec) {
					$text_css = $text_css.'<option value='.$temp_rec.' selected>'.$temp_rec.'</option>';
					$temp_img = $css->img_ico;
				}
				else $text_css = $text_css.'<option value='.$temp_rec.' >'.$temp_rec.'</option>'; 
			}
		}	
	}
		

	$rec['TEMPLATE_SEARH'] = $text_html;
	$rec['TEMPLATE_CSS'] = $text_css;
	
	$rec['TEMPLATE_IMG'] = BASE_URL.'/templates/scene_okbit/sc_templates/'.$rec['TEMPLATE'].'/images/'.$temp_img.'.png';

outHash($rec, $out);

