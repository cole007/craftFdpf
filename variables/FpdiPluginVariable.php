<?php
/**
 * FPDI plugin plugin for Craft CMS
 *
 * FPDI plugin Variable
 *
 * --snip--
 * Craft allows plugins to provide their own template variables, accessible from the {{ craft }} global variable
 * (e.g. {{ craft.pluginName }}).
 *
 * https://craftcms.com/docs/plugins/variables
 * --snip--
 *
 * @author    @cole007
 * @copyright Copyright (c) 2016 @cole007
 * @link      http://ournameismud.co.uk/
 * @package   FpdiPlugin
 * @since     1.0.0
 */

namespace Craft;

class FpdiPluginVariable
{
    /**
     * Whatever you want to output to a Twig tempate can go into a Variable method. You can have as many variable
     * functions as you want.  From any Twig template, call it like this:
     *
     *     {{ craft.fpdiPlugin.exampleVariable }}
     *
     * Or, if your variable requires input from Twig:
     *
     *     {{ craft.fpdiPlugin.exampleVariable(twigValue) }}
     */
	private  function px2mm($px)
	{
		return ($px * 25.4);
	}
	public function getPath($id)
	{
		$folder = craft()->assets->getFolderById($id);
		$source = craft()->assetSources->getSourceById($folder->sourceId);
		return $source->settings['path'];
	}
	public function doPDF($id = null, $imgId)
    {
        $settings = craft()->plugins->getPlugin('fpdiplugin')->getSettings();
    	$entryC = craft()->elements->getCriteria(ElementType::Entry);
    	$entryC->id = $id;
    	$entries = $entryC->find();
    	$entry = $entries[0];

    	$assetId = $settings->pdfTemplate[0];    	
    	
    	$asset = craft()->assets->getFileById($assetId);
    	$src = $this->getPath($asset->folderId) . $asset->path;
    	
    	$img = craft()->assets->getFileById($imgId);
    	$imgSrc = $this->getPath($img->folderId) . $img->path;
    	
    	// $pdf = new \FPDI();        
        $pdf = new \PDF_TextBox();
        
        $pdf->AddPage('P','A4');        
        $pdf->setSourceFile($src);
        
        $tplIdx = $pdf->importPage(1);
        

        $title = iconv('utf-8', 'cp1252//IGNORE', $entry->title);        
       	
       	$pdf->useTemplate($tplIdx, 0, -1, 210);
       	$pdf->SetAutoPageBreak(false);
        $pdf->SetTextColor(255, 255, 255);              

        $pdf->Image($imgSrc, 85, 65, '', 82, '', $entry->url, '', false, 300);

        $pdf->SetFont('HelveticaN','',24);
        $pdf->SetXY(12,18);
        $pdf->drawTextBox($title, 57, 50, 1);


        $pdf->SetFont('HelveticaN','',12);
        $pdf->SetXY(90,155);
        $pdf->SetTextColor(78, 128, 156);
        $pdf->drawTextBox(iconv('utf-8', 'cp1252//IGNORE', $entry->productDescription), 109, 54, 1.25);
        
        $pdf->SetXY(90,215);
        $pdf->drawTextBox('Product Number: ' . iconv('utf-8', 'cp1252//IGNORE', $entry->productNumber), 109, 20, 1.25);

        $features = "";
        foreach($entry->productFeatures AS $line) {
        	$features .= '• ' . $line['feature'] . "\n";
        }
        $pdf->SetFont('HelveticaN','L',11);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetXY(90,225);
        $pdf->drawTextBox(iconv('utf-8', 'cp1252//IGNORE', $features), 109, 50, 1.25);
        
        
        $pdf->endTemplate();

        $pdf->AddPage('P','A4');    
        $tplIdx = $pdf->importPage(2);
        $pdf->useTemplate($tplIdx, 0, -1, 210);

        $divis = ceil(count($entry->productSpecification)/2);
        
        setlocale(LC_CTYPE, 'nl_BE.utf8');
        
        $spec = $entry->productSpecification;
        
        $x = 15;
		$y = 30;  
    	foreach ($spec as $key=>$row) {
    		// split to the second batch of rows
    		if ($key == $divis) {
    			$y = 30;
    			$x += 95;
    		}
    		$pdf->SetFont('HelveticaN','M',10);
    		$pdf->SetTextColor(78, 128, 156);
    		$pdf->SetXY($x,$y);
    		$pdf->write(0,iconv('utf-8', 'cp1252//IGNORE', $row->specHeading));
    		$y += 3;
    		foreach ($row->specDetails AS $item) {
    			$pdf->SetFont('HelveticaN','L',10);
    			$pdf->SetTextColor(0, 0, 0);
    			
    			$pdf->SetXY($x,$y);        			
    			$str = trim($item['leftAligned']);
    			$str = iconv('utf-8', 'cp1252//IGNORE', $str);
    			$pdf->Cell( 42.5, 5, $str, 0, 0, 'L' );
    			$pdf->SetXY(($x + 40),$y);        			
    			$str = trim($item['rightAligned']);
    			$str = iconv('utf-8', 'cp1252//IGNORE', $str);
    			$pdf->Cell( 42.5, 5, $str, 0, 0, 'R' );
    			$y += 5;
    		}

    		$y += 5;
    	}        	     

		$pdf->SetFont('HelveticaN','L',8);
		$pdf->SetTextColor(0, 0, 0);
		$pdf->SetXY(11,271);
		$pdf->write(0,'For further information contact: i-Tech.Tooling@Subsea7.com', 'mailto:i-Tech.Tooling@Subsea7.com');

		$pdf->SetFont('HelveticaN','B',12);
		$pdf->SetXY(11,278);
		$pdf->write(0,$_SERVER['SERVER_NAME'],craft()->getSiteUrl());

        $pdf->SetFont('HelveticaN','L',7);
		$pdf->SetXY(11,283);
		$pdf->write(0,iconv('utf-8','cp1252','© Subsea 7, ' . date('Y') . '. Information correct at time of going to press.'));

        $pdf->endTemplate();

        $pdf->Output('I',$entry->slug);
        
    }
}