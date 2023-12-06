<?php  
global $wpdb;
$upload = wp_upload_dir();
$upload_dir = $upload['basedir'];
$upload_dir = $upload_dir . '/koci-pdf-results';
if (! is_dir($upload_dir)) 
{
	mkdir( $upload_dir, 0775 );
}

$current_user = wp_get_current_user();
$getCurrentDate =  date("F j, Y");
$getCurrentTimeStamp = strtotime("now");
$getCurrentTimeStamp = date('F j, Y - H:i:s',$getCurrentTimeStamp);

if ( 0 != $current_user->ID ) 
{
	$getUserFirstName = esc_html( $current_user->user_firstname ) ;
	$getUserLastName = esc_html( $current_user->user_lastname ) ;
}
if(empty($getUserFirstName) && empty($getUserLastName))
{
	$getFinalName = esc_html( $current_user->user_login  ) ;
}
else
{
	$getFinalName = $getUserFirstName.' '.$getUserLastName;
}
$getFinalName = ucwords($getFinalName);
$getCurrentUserID = $current_user->ID;

use setasign\Fpdi\Fpdi;

require_once('fpdf/fpdf.php');
require_once('fpdi2/src/autoload.php'); 
require_once('fpdi_pdf-parser2/src/autoload.php');


$generatePdfPath = plugin_dir_path( __FILE__ );
$sourcePDF = $generatePdfPath.'client-sample-koci-updated.pdf';

$generatePdfUrl = plugin_dir_url( __FILE__ );
$source2ndPage = $generatePdfUrl.'KOCI-Copyright.jpg';
$source25thPage = $generatePdfUrl.'Back.Color_KOCI.1-1.jpg';
$source26thPage = $generatePdfUrl.'Back.Color_KOCI.2-1.jpg';

$downloadPDF = "$upload_dir/Personalized KOCI Report for $getFinalName - $getCurrentTimeStamp.pdf";
$upload_base_url = $upload['baseurl'];
$dynamicDownloadPdfLink = "$upload_base_url/koci-pdf-results/Personalized KOCI Report for $getFinalName - $getCurrentTimeStamp.pdf";

$pdf = new FPDI('Portrait','mm',array(216,279));

$pdf->AddFont('Textile','','Textile_Regular_1.php');
$pdf->AddFont('Formata-Regular','','Formata-Regular.php');
$pdf->AddFont('Formata-Medium','','Formata-Medium.php');
//$pdf->AddFont('Formata-Medium','','Formata-Medium.php');
$pdf->SetTextColor(26, 25, 21);

$pageCount = $pdf->setSourceFile($sourcePDF);
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) 
{
	$tplIdx = $pdf->importPage($pageNo);
	$pdf->AddPage();
	$pdf->useTemplate($tplIdx,0,0,216);
	
	if($pageNo == 1)
	{
		$pdf->SetFont('Textile');
		$pdf->SetTextColor(34, 35, 91);
		
		//Personalized Text
		$pdf->SetFontSize(16);
		$stringPersonalized = "Personalized";
		$pdf->SetXY(88, 102);
		$pdf->Multicell(80, 6, $stringPersonalized);
		
		//Koci Report Text
		$pdf->SetFontSize(18);
		$stringKociReport = "KOCI Report for";
		$pdf->SetXY(80, 112);
		$pdf->Multicell(0, 6, $stringKociReport);	
					
		//UserName Text
		$stringUserName = "$getFinalName";
		$pdf->SetXY(10, 138);
		$pdf->MultiCell(0,0,$stringUserName,0,'C');
		
		//Current Date
		$getCurrentDate =  date("F j, Y"); 
		$pdf->SetXY(10, 165);
		$pdf->MultiCell(0,0,$getCurrentDate,0,'C');
	}
	
	if($pageNo == 2)
	{
		$pdf->Image($source2ndPage,0,0,216);
	}
	
	if($pageNo == 5)
	{
		$pdf->SetFont('Formata-Regular');
		$pdf->SetFontSize(12);
		$pdf->SetTextColor(149, 139, 132);
		
		//Culture Styling
		//$stringCulture = "4 = L";
		$stringCulture = "$getCultureScore";
		if (strpos($stringCulture, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringCulture, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringCultureLen = strlen($stringCulture);
		if($stringCultureLen == 6)
		{
			$pdf->SetXY(66, 60);
		}
		else
		{
			$pdf->SetXY(68, 60);
		}
		$pdf->Multicell(80, 6, $stringCulture);
		
		//Skills Styling
		//$stringSkills = "4 = L";
		$stringSkills = "$getSkillsScore";
		if (strpos($stringSkills, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringSkills, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringSkillsLen = strlen($stringSkills);
		if($stringSkillsLen == 6)
		{
			$pdf->SetXY(106, 47);
		}
		else
		{
			$pdf->SetXY(108, 47);
		}
		$pdf->Multicell(80, 6, $stringSkills);
		
		//Team Styling
		//$stringTeam = "6 = L";
		$stringTeam = "$getTeamsScore";
		if (strpos($stringTeam, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringTeam, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringTeamLen = strlen($stringTeam);
		if($stringTeamLen == 6)
		{
			$pdf->SetXY(148, 60);
		}
		else
		{
			$pdf->SetXY(150, 60);
		}
		$pdf->Multicell(80, 6, $stringTeam);
		
		//Strategy Styling
		//$stringStrategy = "10.5 = M";
		$stringStrategy = "$getStrategyScore";
		if (strpos($stringStrategy, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringStrategy, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringStrategyLen = strlen($stringStrategy);
		if($stringStrategyLen == 6)
		{
			$pdf->SetXY(159, 97);
		}
		else
		{
			$pdf->SetXY(161, 97);
		}
		$pdf->Multicell(80, 6, $stringStrategy);
		
		//Reward Styling
		//$stringReward = "14 = H";
		$stringReward = "$getRewardScore";
		if (strpos($stringReward, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringReward, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringRewardLen = strlen($stringReward);
		if($stringRewardLen == 6)
		{
			$pdf->SetXY(148, 137);
		}
		else
		{
			$pdf->SetXY(150, 137);
		}
		$pdf->Multicell(80, 6, $stringReward);
		
		//Gradual Styling
		//$stringGradual = "14 = H";
		$stringGradual = "$getGradualScore";
		if (strpos($stringGradual, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringGradual, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringGradualLen = strlen($stringGradual);
		if($stringGradualLen == 6)
		{
			$pdf->SetXY(106, 150);
		}
		else
		{
			$pdf->SetXY(108, 150);
		}
		$pdf->Multicell(80, 6, $stringGradual);
		
		//Radical Styling
		//$stringRadical = "12 = H";
		$stringRadical = "$getRadicalScore";
		if (strpos($stringRadical, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringRadical, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringRadicalLen = strlen($stringRadical);
		if($stringRadicalLen == 6)
		{
			$pdf->SetXY(66, 137);
		}
		else
		{
			$pdf->SetXY(68, 137);
		}
		$pdf->Multicell(80, 6, $stringRadical);
		
		//Learning Styling
		//$stringLearning = "9 = M";
		$stringLearning = "$getLearningScore";
		if (strpos($stringLearning, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringLearning, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringLearningLen = strlen($stringLearning);
		if($stringLearningLen == 6)
		{
			$pdf->SetXY(54, 97);
		}
		else
		{
			$pdf->SetXY(56, 97);
		}
		$pdf->Multicell(80, 6, $stringLearning);
		
		//Competing Styling
		//$stringCompeting = "9 = L";
		$stringCompeting = "$getCompetingScore";
		if (strpos($stringCompeting, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringCompeting, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringCompetingLen = strlen($stringCompeting);
		if($stringCompetingLen == 6)
		{
			$pdf->SetXY(89, 85);
		}
		else
		{
			$pdf->SetXY(90, 85);
		}
		$pdf->Multicell(80, 6, $stringCompeting);
		
		//Collaborating Styling
		//$stringCollaborating = "27 = M";
		$stringCollaborating = "$getCollaboratingScore";
		if (strpos($stringCollaborating, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringCollaborating, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringCollaboratingLen = strlen($stringCollaborating);
		if($stringCollaboratingLen == 6)
		{
			$pdf->SetXY(125, 85);
		}
		else
		{
			$pdf->SetXY(127, 85);
		}
		$pdf->Multicell(80, 6, $stringCollaborating);
		
		//Compromising Styling
		//$stringCompromising = "18 = L";
		$stringCompromising = "$getCompromisingScore";
		if (strpos($stringCompromising, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringCompromising, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringCompromisingLen = strlen($stringCompromising);
		if($stringCompromisingLen == 6)
		{
			$pdf->SetXY(107, 100);
		}
		else
		{
			$pdf->SetXY(109, 100);
		}
		$pdf->Multicell(80, 6, $stringCompromising);
		
		//Avoiding Styling
		//$stringAvoiding = "45 = H";
		$stringAvoiding = "$getAvoidingScore";
		if (strpos($stringAvoiding, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringAvoiding, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringAvoidingLen = strlen($stringAvoiding);
		if($stringAvoidingLen == 6)
		{
			$pdf->SetXY(88, 116);
		}
		else
		{
			$pdf->SetXY(90, 116);
		}
		$pdf->Multicell(80, 6, $stringAvoiding);
		
		//Accommodating Styling
		//$stringAccommodating = "36 = H";
		$stringAccommodating = "$getAccommodatingScore";
		if (strpos($stringAccommodating, 'L') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(149, 139, 132);
		}
		elseif (strpos($stringAccommodating, 'M') !== false) 
		{
			$pdf->SetFont('Formata-Regular');
			$pdf->SetTextColor(26, 25, 21);
		}
		else
		{
			$pdf->SetFont('Formata-Medium');
			$pdf->SetTextColor(26, 25, 21);
		}
		
		$stringAccommodatingLen = strlen($stringAccommodating);
		if($stringAccommodatingLen == 6)
		{
			$pdf->SetXY(125, 116);
		}
		else
		{
			$pdf->SetXY(127, 116);
		}
		$pdf->Multicell(80, 6, $stringAccommodating);

		// Textile Font
		$pdf->SetFont('Textile');
		$pdf->SetTextColor(26, 25, 21);
		$pdf->SetFontSize(16);
		
		//UserName Text
		$stringUserName = "$getFinalName";
		$pdf->SetXY(20, 210);
		$pdf->MultiCell(0,0,$stringUserName,0,'C');
		
		//Current Date
		$pdf->SetFontSize(16);
		$pdf->SetXY(20, 225);
		$pdf->MultiCell(0,0,$getCurrentDate,0,'C');
	}
	
	if($pageNo == 25)
	{
		$pdf->Image($source25thPage,0,0,216);
	}
	if($pageNo == 26)
	{
		$pdf->Image($source26thPage,0,0,216);
	}
}
ob_clean();
//$pdf->Output('tets.pdf', "D");
$pdf->Output($downloadPDF, "F");
//generatePDF("client-sample-koci-updated.pdf", "KOCI-Graphing-Intepreting-Results-For-Casey-Riney.pdf");
?>