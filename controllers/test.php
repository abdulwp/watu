<?php  
use setasign\Fpdi\Fpdi;

require_once('fpdf/fpdf.php');
require_once('fpdi2/src/autoload.php'); 
require_once('fpdi_pdf-parser2/src/autoload.php');


$source = '/nas/content/live/kilmanndev/wp-content/plugins/watupro/controllers/client-sample-koci-updated.pdf';
$output = 'KOCI-Graphing-Intepreting-Results-For-Casey-Riney.pdf';

$pdf = new FPDI('Portrait','mm',array(216,279));

$pdf->AddFont('Textile','','Textile_Regular.php');
$pdf->SetFont('Textile');

$pageCount = $pdf->setSourceFile($source);
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) 
{
	$tplIdx = $pdf->importPage($pageNo);
	$pdf->AddPage();
	$pdf->useTemplate($tplIdx,0,0,216);
	
	if($pageNo == 1)
	{
		$pdf->SetTextColor(34, 35, 91);
		
		//Personalized Text
		$pdf->SetFontSize(16);
		$stringPersonalized = "Personalized";
		$pdf->SetXY(88, 102);
		$pdf->Multicell(80, 6, $stringPersonalized);
		
		//Koci Report Text
		$pdf->SetFontSize(18);
		$stringKociReport = "Koci Report for";
		$pdf->SetXY(83, 112);
		$pdf->Multicell(0, 6, $stringKociReport);	
					
		//UserName Text
		$stringUserName = "Casey Riney";
		$pdf->SetXY(10, 138);
		$pdf->MultiCell(0,0,$stringUserName,0,'C');
		
		//Current Date
		$getCurrentDate =  date("M j, Y");
		$pdf->SetXY(10, 165);
		$pdf->MultiCell(0,0,$getCurrentDate,0,'C');
	}
	
	if($pageNo == 5)
	{
		// Formata Font Bold Italic
		$pdf->AddFont('Formata-BoldItalic','','Formata-BoldItalic.php');
		$pdf->SetFont('Formata-BoldItalic');
		
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetFontSize(12);
		
		//Culture Styling
		$stringCulture = "4 = L";
		$pdf->SetXY(68, 60);
		$pdf->Multicell(80, 6, $stringCulture);
		
		//Skills Styling
		$stringSkills = "4 = L";
		$pdf->SetXY(108, 47);
		$pdf->Multicell(80, 6, $stringSkills);
		
		//Team Styling
		$stringTeam = "6 = L";
		$pdf->SetXY(150, 60);
		$pdf->Multicell(80, 6, $stringTeam);
		
		//Strategy Styling
		$stringStrategy = "10.5 = M";
		$pdf->SetXY(157, 97);
		$pdf->Multicell(80, 6, $stringStrategy);
		
		//Reward Styling
		$stringReward = "14 = H";
		$pdf->SetXY(147, 137);
		$pdf->Multicell(80, 6, $stringReward);
		
		//Gradual Styling
		$stringGradual = "14 = H";
		$pdf->SetXY(106, 150);
		$pdf->Multicell(80, 6, $stringGradual);
		
		//Radical Styling
		$stringRadical = "12 = H";
		$pdf->SetXY(66, 137);
		$pdf->Multicell(80, 6, $stringRadical);
		
		//Learning Styling
		$stringLearning = "9 = M";
		$pdf->SetXY(54, 97);
		$pdf->Multicell(80, 6, $stringLearning);
		
		//Competing Styling
		$stringCompeting = "9 = L";
		$pdf->SetXY(90, 85);
		$pdf->Multicell(80, 6, $stringCompeting);
		
		//Collaborating Styling
		$stringCollaborating = "27 = M";
		$pdf->SetXY(124, 85);
		$pdf->Multicell(80, 6, $stringCollaborating);
		
		//Compromising Styling
		$stringCompromising = "18 = L";
		$pdf->SetXY(107, 100);
		$pdf->Multicell(80, 6, $stringCompromising);
		
		//Avoiding Styling
		$stringAvoiding = "45 = H";
		$pdf->SetXY(88, 116);
		$pdf->Multicell(80, 6, $stringAvoiding);
		
		//Accommodating Styling
		$stringAccommodating = "36 = H";
		$pdf->SetXY(125, 116);
		$pdf->Multicell(80, 6, $stringAccommodating);

		// Textile Font
		$pdf->AddFont('Textile','','Textile_Regular.php');
		$pdf->SetFont('Textile');
		
		//UserName Text
		$pdf->SetFontSize(16);
		$stringUserName = "4 = L Casey Riney";
		$pdf->SetXY(20, 210);
		$pdf->MultiCell(0,0,$stringUserName,0,'C');
		
		//Current Date
		$pdf->SetFontSize(16);
		$getCurrentDate =  date("M j, Y");
		$pdf->SetXY(20, 225);
		$pdf->MultiCell(0,0,$getCurrentDate,0,'C');
	}
}
ob_clean();
$pdf->Output('tets.pdf', "D");
$pdf->Output($output, "F");
//generatePDF("client-sample-koci-updated.pdf", "KOCI-Graphing-Intepreting-Results-For-Casey-Riney.pdf");
?>