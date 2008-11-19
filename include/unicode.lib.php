<?php

//# ������� ����������� ����, ��� ������ $str ����������� UTF-8 (�������)
//# ���������� true ���� UTF-8 ��� false ���� ASCII
//# �������� ������� �� ������� ��������.
//# ����� ������ ��������� �������������, �������� ������ return false;
function detect_utf($Str)
{
	for($i = 0; $i < strlen($Str); $i++) {
		if(ord($Str[$i]) < 0x80) $n = 0; # 0bbbbbbb
		elseif((ord($Str[$i]) & 0xE0) == 0xC0) $n = 1; # 110bbbbb
		elseif((ord($Str[$i]) & 0xF0) == 0xE0) $n = 2; # 1110bbbb
		elseif((ord($Str[$i]) & 0xF0) == 0xF0) $n = 3; # 1111bbbb
		else return false; # Does not match any model
		for($j = 0; $j < $n; $j++) { # n octets that match 10bbbbbb follow ?
			if((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80)) return false;
		}
	}
	return true;
}

//##
//## ������������� unicode UTF-8 -> win1251
//##


function utf8_win($s)
{
	$out = "";
	$c1 = "";
	$byte2 = false;
	for($c = 0; $c < strlen($s); $c++) {
		$i = ord($s[$c]);
		if($i <= 127) $out .= $s[$c];
		if($byte2) {
			$new_c2 = ($c1 & 3) * 64 + ($i & 63);
			$new_c1 = ($c1 >> 2) & 5;
			$new_i = $new_c1 * 256 + $new_c2;
			if($new_i == 1025) {
				$out_i = 168;
			}
			else {
				if($new_i == 1105) {
					$out_i = 184;
				}
				else {
					$out_i = $new_i - 848;
				}
			}
			$out .= chr($out_i);
			$byte2 = false;
		}
		if(($i >> 5) == 6) {
			$c1 = $i;
			$byte2 = true;
		}
	}
	return $out;
}

//# ��������������� ������ CP1251 � UNICODE
//# ��� �������� ������� �������� ���������� �� ����������
//# ������� ��������� �� ������� ��������
//# (����� ���������� - ������ "&x0430" ("�" ���.) ������ "a" ("a" eng.))


function unicod($in_text)
{
	$rus = "�������������������";
	$eng = "ABE3KMHOPCTXaeopcyx";
	
	$output = "";
	$other[1025] = "�";
	$other[1105] = "�";
	$other[1028] = "�";
	$other[1108] = "�";
	$other[1030] = "I";
	$other[1110] = "i";
	$other[1031] = "�";
	$other[1111] = "�";
	$l = strlen($rus);
	for($i = 0; $i < strlen($in_text); $i++) {
		$rep = 0;
		$c = substr($in_text, $i, 1);
		for($j = 0; $j < $l; $j++) {
			if($c == substr($rus, $j, 1)) {
				$output .= substr($eng, $j, 1);
				$rep = 1;
				break;
			}
		}
		if(!$rep) {
			if(ord($c) > 191) {
				$output .= "&#" . (ord($c) + 848) . ";";
			}
			else {
				if(array_search($c, $other) === false) {
					$output .= $c;
				}
				else {
					$output .= "&#" . array_search($c, $other) . ";";
				}
			}
		}
	}
	return $output;
}

//##
//## ������������� win1251 -> unicode (UTF-8)
//## ���� ����� ��� � ����, ������ ��� �������� �����...


function win_utf8($in_text)
{
	$output = "";
	$other[1025] = "�";
	$other[1105] = "�";
	$other[1028] = "�";
	$other[1108] = "�";
	$other[1030] = "I";
	$other[1110] = "i";
	$other[1031] = "�";
	$other[1111] = "�";
	
	for($i = 0; $i < strlen($in_text); $i++) {
		if(ord($in_text{$i}) > 191) {
			$output .= "&#" . (ord($in_text{$i}) + 848) . ";";
		}
		else {
			if(array_search($in_text{$i}, $other) === false) {
				$output .= $in_text{$i};
			}
			else {
				$output .= "&#" . array_search($in_text{$i}, $other) . ";";
			}
		}
	}
	return $output;
}

//##
//## ������������ Win1251 -> unicode (�� UTF-8 !!!)
//##


function win2utf($string)
{
	$string = ereg_replace("�", "&#x0430;", $string);
	$string = ereg_replace("�", "&#x0431;", $string);
	$string = ereg_replace("�", "&#x0432;", $string);
	$string = ereg_replace("�", "&#x0433;", $string);
	$string = ereg_replace("�", "&#x0434;", $string);
	$string = ereg_replace("�", "&#x0435;", $string);
	$string = ereg_replace("�", "&#x0451;", $string);
	$string = ereg_replace("�", "&#x0436;", $string);
	$string = ereg_replace("�", "&#x0437;", $string);
	$string = ereg_replace("�", "&#x0438;", $string);
	$string = ereg_replace("�", "&#x0439;", $string);
	$string = ereg_replace("�", "&#x043A;", $string);
	$string = ereg_replace("�", "&#x043B;", $string);
	$string = ereg_replace("�", "&#x043C;", $string);
	$string = ereg_replace("�", "&#x043D;", $string);
	$string = ereg_replace("�", "&#x043E;", $string);
	$string = ereg_replace("�", "&#x043F;", $string);
	$string = ereg_replace("�", "&#x0440;", $string);
	$string = ereg_replace("�", "&#x0441;", $string);
	$string = ereg_replace("�", "&#x0442;", $string);
	$string = ereg_replace("�", "&#x0443;", $string);
	$string = ereg_replace("�", "&#x0444;", $string);
	$string = ereg_replace("�", "&#x0445;", $string);
	$string = ereg_replace("�", "&#x0446;", $string);
	$string = ereg_replace("�", "&#x0448;", $string);
	$string = ereg_replace("�", "&#x0449;", $string);
	$string = ereg_replace("�", "&#x044A;", $string);
	$string = ereg_replace("�", "&#x044C;", $string);
	$string = ereg_replace("�", "&#x044D;", $string);
	$string = ereg_replace("�", "&#x044E;", $string);
	$string = ereg_replace("�", "&#x044F;", $string);
	$string = ereg_replace("�", "&#x0447;", $string);
	$string = ereg_replace("�", "&#x044B;", $string);
	$string = ereg_replace("�", "&#x0410;", $string);
	$string = ereg_replace("�", "&#x0411;", $string);
	$string = ereg_replace("�", "&#x0412;", $string);
	$string = ereg_replace("�", "&#x0413;", $string);
	$string = ereg_replace("�", "&#x0414;", $string);
	$string = ereg_replace("�", "&#x0415;", $string);
	$string = ereg_replace("�", "&#x041;", $string);
	$string = ereg_replace("�", "&#x0416;", $string);
	$string = ereg_replace("�", "&#x0417;", $string);
	$string = ereg_replace("�", "&#x0418;", $string);
	$string = ereg_replace("�", "&#x0419;", $string);
	$string = ereg_replace("�", "&#x041A;", $string);
	$string = ereg_replace("�", "&#x041B;", $string);
	$string = ereg_replace("�", "&#x041C;", $string);
	$string = ereg_replace("�", "&#x041D;", $string);
	$string = ereg_replace("�", "&#x041E;", $string);
	$string = ereg_replace("�", "&#x041F;", $string);
	$string = ereg_replace("�", "&#x0420;", $string);
	$string = ereg_replace("�", "&#x0421;", $string);
	$string = ereg_replace("�", "&#x0422;", $string);
	$string = ereg_replace("�", "&#x0423;", $string);
	$string = ereg_replace("�", "&#x0424;", $string);
	$string = ereg_replace("�", "&#x0425;", $string);
	$string = ereg_replace("�", "&#x0426;", $string);
	$string = ereg_replace("�", "&#x0428;", $string);
	$string = ereg_replace("�", "&#x0429;", $string);
	$string = ereg_replace("�", "&#x042A;", $string);
	$string = ereg_replace("�", "&#x042C;", $string);
	$string = ereg_replace("�", "&#x042D;", $string);
	$string = ereg_replace("�", "&#x042E;", $string);
	$string = ereg_replace("�", "&#x042F;", $string);
	$string = ereg_replace("�", "&#x0427;", $string);
	$string = ereg_replace("�", "&#x042B;", $string);
	return $string;
}

//# ��������������� ������ $strin ���� ��� ����������� UTF
//# �.�. ����: (%u041C%u0435%u043B) � Win-1251
//# ����� ���������...


function utf16win($strin)
{
	$strin = ereg_replace("%u0430", "�", $strin);
	$strin = ereg_replace("%u0431", "�", $strin);
	$strin = ereg_replace("%u0432", "�", $strin);
	$strin = ereg_replace("%u0433", "�", $strin);
	$strin = ereg_replace("%u0434", "�", $strin);
	$strin = ereg_replace("%u0435", "�", $strin);
	$strin = ereg_replace("%u0451", "�", $strin);
	$strin = ereg_replace("%u0436", "�", $strin);
	$strin = ereg_replace("%u0437", "�", $strin);
	$strin = ereg_replace("%u0438", "�", $strin);
	$strin = ereg_replace("%u0439", "�", $strin);
	$strin = ereg_replace("%u043A", "�", $strin);
	$strin = ereg_replace("%u043B", "�", $strin);
	$strin = ereg_replace("%u043C", "�", $strin);
	$strin = ereg_replace("%u043D", "�", $strin);
	$strin = ereg_replace("%u043E", "�", $strin);
	$strin = ereg_replace("%u043F", "�", $strin);
	$strin = ereg_replace("%u0440", "�", $strin);
	$strin = ereg_replace("%u0441", "�", $strin);
	$strin = ereg_replace("%u0442", "�", $strin);
	$strin = ereg_replace("%u0443", "�", $strin);
	$strin = ereg_replace("%u0444", "�", $strin);
	$strin = ereg_replace("%u0445", "�", $strin);
	$strin = ereg_replace("%u0446", "�", $strin);
	$strin = ereg_replace("%u0448", "�", $strin);
	$strin = ereg_replace("%u0449", "�", $strin);
	$strin = ereg_replace("%u044A", "�", $strin);
	$strin = ereg_replace("%u044C", "�", $strin);
	$strin = ereg_replace("%u044D", "�", $strin);
	$strin = ereg_replace("%u044E", "�", $strin);
	$strin = ereg_replace("%u044F", "�", $strin);
	$strin = ereg_replace("%u0447", "�", $strin);
	$strin = ereg_replace("%u044B", "�", $strin);
	$strin = ereg_replace("%u0410", "�", $strin);
	$strin = ereg_replace("%u0411", "�", $strin);
	$strin = ereg_replace("%u0412", "�", $strin);
	$strin = ereg_replace("%u0413", "�", $strin);
	$strin = ereg_replace("%u0414", "�", $strin);
	$strin = ereg_replace("%u0415", "�", $strin);
	$strin = ereg_replace("%u0416", "�", $strin);
	$strin = ereg_replace("%u0417", "�", $strin);
	$strin = ereg_replace("%u0418", "�", $strin);
	$strin = ereg_replace("%u0419", "�", $strin);
	$strin = ereg_replace("%u041A", "�", $strin);
	$strin = ereg_replace("%u041B", "�", $strin);
	$strin = ereg_replace("%u041C", "�", $strin);
	$strin = ereg_replace("%u041D", "�", $strin);
	$strin = ereg_replace("%u041E", "�", $strin);
	$strin = ereg_replace("%u041F", "�", $strin);
	$strin = ereg_replace("%u0420", "�", $strin);
	$strin = ereg_replace("%u0421", "�", $strin);
	$strin = ereg_replace("%u0422", "�", $strin);
	$strin = ereg_replace("%u0423", "�", $strin);
	$strin = ereg_replace("%u0424", "�", $strin);
	$strin = ereg_replace("%u0425", "�", $strin);
	$strin = ereg_replace("%u0426", "�", $strin);
	$strin = ereg_replace("%u0428", "�", $strin);
	$strin = ereg_replace("%u0429", "�", $strin);
	$strin = ereg_replace("%u042A", "�", $strin);
	$strin = ereg_replace("%u042C", "�", $strin);
	$strin = ereg_replace("%u042D", "�", $strin);
	$strin = ereg_replace("%u042E", "�", $strin);
	$strin = ereg_replace("%u042F", "�", $strin);
	$strin = ereg_replace("%u0427", "�", $strin);
	$strin = ereg_replace("%u042B", "�", $strin);
	$strin = ereg_replace("%u041", "�", $strin);
	return $strin;
}

//# ��������������� ������ $strin ���� ��� ����������� UTF
//# �.�. ����: (&#x041C;&#x0435;&#x043B;) � Win-1251
//# ����� ���������...


function utf8win($strin)
{
	$strin = ereg_replace("&#x0430;", "�", $strin);
	$strin = ereg_replace("&#x0431;", "�", $strin);
	$strin = ereg_replace("&#x0432;", "�", $strin);
	$strin = ereg_replace("&#x0433;", "�", $strin);
	$strin = ereg_replace("&#x0434;", "�", $strin);
	$strin = ereg_replace("&#x0435;", "�", $strin);
	$strin = ereg_replace("&#x0451;", "�", $strin);
	$strin = ereg_replace("&#x0436;", "�", $strin);
	$strin = ereg_replace("&#x0437;", "�", $strin);
	$strin = ereg_replace("&#x0438;", "�", $strin);
	$strin = ereg_replace("&#x0439;", "�", $strin);
	$strin = ereg_replace("&#x043A;", "�", $strin);
	$strin = ereg_replace("&#x043B;", "�", $strin);
	$strin = ereg_replace("&#x043C;", "�", $strin);
	$strin = ereg_replace("&#x043D;", "�", $strin);
	$strin = ereg_replace("&#x043E;", "�", $strin);
	$strin = ereg_replace("&#x043F;", "�", $strin);
	$strin = ereg_replace("&#x0440;", "�", $strin);
	$strin = ereg_replace("&#x0441;", "�", $strin);
	$strin = ereg_replace("&#x0442;", "�", $strin);
	$strin = ereg_replace("&#x0443;", "�", $strin);
	$strin = ereg_replace("&#x0444;", "�", $strin);
	$strin = ereg_replace("&#x0445;", "�", $strin);
	$strin = ereg_replace("&#x0446;", "�", $strin);
	$strin = ereg_replace("&#x0448;", "�", $strin);
	$strin = ereg_replace("&#x0449;", "�", $strin);
	$strin = ereg_replace("&#x044A;", "�", $strin);
	$strin = ereg_replace("&#x044C;", "�", $strin);
	$strin = ereg_replace("&#x044D;", "�", $strin);
	$strin = ereg_replace("&#x044E;", "�", $strin);
	$strin = ereg_replace("&#x044F;", "�", $strin);
	$strin = ereg_replace("&#x0447;", "�", $strin);
	$strin = ereg_replace("&#x044B;", "�", $strin);
	$strin = ereg_replace("&#x0410;", "�", $strin);
	$strin = ereg_replace("&#x0411;", "�", $strin);
	$strin = ereg_replace("&#x0412;", "�", $strin);
	$strin = ereg_replace("&#x0413;", "�", $strin);
	$strin = ereg_replace("&#x0414;", "�", $strin);
	$strin = ereg_replace("&#x0415;", "�", $strin);
	$strin = ereg_replace("&#x0416;", "�", $strin);
	$strin = ereg_replace("&#x0417;", "�", $strin);
	$strin = ereg_replace("&#x0418;", "�", $strin);
	$strin = ereg_replace("&#x0419;", "�", $strin);
	$strin = ereg_replace("&#x041A;", "�", $strin);
	$strin = ereg_replace("&#x041B;", "�", $strin);
	$strin = ereg_replace("&#x041C;", "�", $strin);
	$strin = ereg_replace("&#x041D;", "�", $strin);
	$strin = ereg_replace("&#x041E;", "�", $strin);
	$strin = ereg_replace("&#x041F;", "�", $strin);
	$strin = ereg_replace("&#x0420;", "�", $strin);
	$strin = ereg_replace("&#x0421;", "�", $strin);
	$strin = ereg_replace("&#x0422;", "�", $strin);
	$strin = ereg_replace("&#x0423;", "�", $strin);
	$strin = ereg_replace("&#x0424;", "�", $strin);
	$strin = ereg_replace("&#x0425;", "�", $strin);
	$strin = ereg_replace("&#x0426;", "�", $strin);
	$strin = ereg_replace("&#x0428;", "�", $strin);
	$strin = ereg_replace("&#x0429;", "�", $strin);
	$strin = ereg_replace("&#x042A;", "�", $strin);
	$strin = ereg_replace("&#x042C;", "�", $strin);
	$strin = ereg_replace("&#x042D;", "�", $strin);
	$strin = ereg_replace("&#x042E;", "�", $strin);
	$strin = ereg_replace("&#x042F;", "�", $strin);
	$strin = ereg_replace("&#x0427;", "�", $strin);
	$strin = ereg_replace("&#x042B;", "�", $strin);
	$strin = ereg_replace("&#x041;", "�", $strin);
	return $strin;
}

//##
//## ������������ ������� ����� � ��������
//##


function translite($string)
{
	$string = ereg_replace("�", "e", $string);
	$string = ereg_replace("�", "i", $string);
	$string = ereg_replace("�", "u", $string);
	$string = ereg_replace("�", "'", $string);
	$string = ereg_replace("�", "ch", $string);
	$string = ereg_replace("�", "sh", $string);
	$string = ereg_replace("�", "c", $string);
	$string = ereg_replace("�", "y", $string);
	$string = ereg_replace("�", "k", $string);
	$string = ereg_replace("�", "e", $string);
	$string = ereg_replace("�", "n", $string);
	$string = ereg_replace("�", "g", $string);
	$string = ereg_replace("�", "sh", $string);
	$string = ereg_replace("�", "z", $string);
	$string = ereg_replace("�", "h", $string);
	$string = ereg_replace("�", "'", $string);
	$string = ereg_replace("�", "f", $string);
	$string = ereg_replace("�", "w", $string);
	$string = ereg_replace("�", "v", $string);
	$string = ereg_replace("�", "a", $string);
	$string = ereg_replace("�", "p", $string);
	$string = ereg_replace("�", "r", $string);
	$string = ereg_replace("�", "o", $string);
	$string = ereg_replace("�", "l", $string);
	$string = ereg_replace("�", "d", $string);
	$string = ereg_replace("�", "j", $string);
	$string = ereg_replace("�", "�", $string);
	$string = ereg_replace("�", "y�", $string);
	$string = ereg_replace("�", "s", $string);
	$string = ereg_replace("�", "m", $string);
	$string = ereg_replace("�", "i", $string);
	$string = ereg_replace("�", "t", $string);
	$string = ereg_replace("�", "b", $string);
	$string = ereg_replace("�", "E", $string);
	$string = ereg_replace("�", "I", $string);
	$string = ereg_replace("�", "U", $string);
	$string = ereg_replace("�", "CH", $string);
	$string = ereg_replace("�", "'", $string);
	$string = ereg_replace("�", "SH", $string);
	$string = ereg_replace("�", "C", $string);
	$string = ereg_replace("�", "Y", $string);
	$string = ereg_replace("�", "K", $string);
	$string = ereg_replace("�", "E", $string);
	$string = ereg_replace("�", "N", $string);
	$string = ereg_replace("�", "G", $string);
	$string = ereg_replace("�", "SH", $string);
	$string = ereg_replace("�", "Z", $string);
	$string = ereg_replace("�", "H", $string);
	$string = ereg_replace("�", "'", $string);
	$string = ereg_replace("�", "F", $string);
	$string = ereg_replace("�", "W", $string);
	$string = ereg_replace("�", "V", $string);
	$string = ereg_replace("�", "A", $string);
	$string = ereg_replace("�", "P", $string);
	$string = ereg_replace("�", "R", $string);
	$string = ereg_replace("�", "O", $string);
	$string = ereg_replace("�", "L", $string);
	$string = ereg_replace("�", "D", $string);
	$string = ereg_replace("�", "J", $string);
	$string = ereg_replace("�", "E", $string);
	$string = ereg_replace("�", "YA", $string);
	$string = ereg_replace("�", "S", $string);
	$string = ereg_replace("�", "M", $string);
	$string = ereg_replace("�", "I", $string);
	$string = ereg_replace("�", "T", $string);
	$string = ereg_replace("�", "B", $string);
	return $string;
}

?>
