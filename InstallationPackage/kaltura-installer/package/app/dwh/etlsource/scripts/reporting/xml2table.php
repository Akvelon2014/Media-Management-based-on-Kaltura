<?php
if (count($argv) < 3)
{
      echo count($argv) . " parameters found.\n";
      echo "Usage: php xml2table.php <inputFileName> <outputFileName> [<csvGroupColumns>]\n";
      exit(2);
}

$filename = $argv[1];
$outputFile = $argv[2];
$groupColumns = explode(',',$argv[3]);
$lineSeperator = count($groupColumns) == 1;
$xml = new DOMDocument();
$xml->load($filename);

$tableRows = array();
$rowSpans = array();
$lastRow = array();
$rowIndex = 0;
foreach($xml->documentElement->childNodes as $rowNode)
{
	if($rowNode->nodeName != 'row')
		continue;
		
	$tableRow = array();
	$rowSpan = array();
	foreach($rowNode->childNodes as $field)
	{
		if($field->nodeName != 'field')
			continue;
			
		$name = $field->getAttribute('name');
		$tableRow[$name] = $field->textContent;
		
		if(isset($lastRow[$name]) && $lastRow[$name] == $tableRow[$name])
		{
			$rowSpan[$name] = 0;
			$lastIndex = $rowIndex - 1;
			while($lastIndex >=0 && $rowSpans[$lastIndex][$name] == 0)
				$lastIndex--;
				
			$rowSpans[$lastIndex][$name]++;
		}
		else
		{
			$rowSpan[$name] = 1;
		}
	}
	$lastRow = $tableRow;
	
	$rowSpans[] = $rowSpan;
	$tableRows[] = $tableRow;
	$rowIndex++;
}

$str = '<table border="1">';

$previousRowFirstColumnValue = "";
foreach($tableRows as $index => $tableRow)
{
	$columnIndex = 0;
	if(!$index)
	{
		$str .= '<tr>';
		foreach($tableRow as $col => $val)
			$str .= "<th>$col</th>";
		$str .= '</tr>';
	}
	
	$str .= '<tr>';
	foreach($tableRow as $col => $val)
	{
		if ($columnIndex == 0 && !($val == $previousRowFirstColumnValue) && in_array($col, $groupColumns) && $lineSeperator)
                {
			$str .= "<td colspan=". count($tableRow) . " bgcolor='red'/> </tr><tr>";
			$previousRowFirstColumnValue = $val;
                }
		$rowspan = $rowSpans[$index][$col];
		if($rowspan > 0 && in_array($col, $groupColumns))
			$str .= "<td rowspan=\"$rowspan\">$val</td>";
		elseif(!in_array($col, $groupColumns))
			$str .= "<td>$val</td>";
		$columnIndex++;
	}
	$str .= '</tr>';
}

$str .= '</table>';

file_put_contents($outputFile, $str);
