<?php

//error_reporting(0);
session_start();

if(!isset($_SESSION['usu']) || !isset($_SESSION["n_declaracion"])){
		$direccion = "Location: ../index.php";
		header($direccion);
	}else{
require_once('tcpdf/config/lang/eng.php');
require_once('tcpdf/tcpdf.php');
require_once('../clases/conexion.php');

$db= new conexion();
$link = $db->conectar();
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false); 

// set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Daniel Diaz');
$pdf->SetTitle('V&A');
$pdf->SetSubject('Control');
$pdf->SetKeywords('TCPDF, PDF, reporte, control, MANTENIMIENTO');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING);

// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '1', 90));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
//$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

//set margins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetMargins(10, 10, 10);
//$pdf->SetHeaderMargin();
//$pdf->SetFooterMargin(15);

//set auto page breaks
$pdf->SetAutoPageBreak(TRUE, 5);

//set image scale factor
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO); 

//set some language-dependent strings
//$pdf->setLanguageArray($l); 
// ---------------------------------------------------------
// set font
$pdf->SetFont('helvetica', '', 10);

// add a page

$orientacion="vertical";

//ARREGLO DE MESES PARA MOSTRAR
$meses=array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
// ---------------INICIO DEL REPORTE-----------------

//----------ENCABEZADO CREADO DESPUES DE LA IMPRESION DE LOS ITEMS-----//

//$resultado=mysql_query("select * from factura where numeroretaceo='jor301'",$link);

//INICIO DE CONSULTA 
$resultado=mysql_query("select item.descripcion,item.bultos,item.pesoBruto,item.cuantia as cuantia,(item.cuantia * item.precioUnitario) as fob, 
    factura.numero as factura,factura.idFactRetaceo, item.agrupar,partidaArancelaria,retaceoimpuestos.arancel,retaceoimpuestos.pais
    from item inner join factura on item.idFactura=factura.idFactura inner join retaceoImpuestos on item.idRetaceo=retaceoImpuestos.idRetaceo
    where item.idRetaceo=".  hideunlock($_SESSION["n_declaracion"])." 
        and item.partidaArancelaria=retaceoImpuestos.inciso 
        and item.agrupar=retaceoimpuestos.agrupar
    order by retaceoImpuestos.idItemImp,factura.idFactRetaceo"
            ,$link);

//Variables de Sumatorias Totales
$fobSubt=0;
$fobTotal=0;
//------//
$bultosSubt=0;
$bultosTotal=0;
//------//
$pesoSubt=0;
$pesoTotal=0;
//------//
$cuantiaSubt=0;
$cuantiaTotal=0;
//-----------------------------//

$tempA=0;
$tempB=0;
$NumItem=1;
while($row_exp = mysql_fetch_array($resultado)) //CONSULTA PARA CADA REGISTRO
{
//IMPRESION DE CADA REGISTRO
//PARA HACER UNA AGRUPACION
$FlagAgrupado=$row_exp[7];//PRIMERO SE GUARDA LA BANDERA DE AGRUPADO
$PartidaAgrupada=$row_exp[8];//SEGUNDO SE GUARDA LA PARTIDA ARANCELARIA QUE AGRUPA
$dai=$row_exp[9];
//La agrupacion se valida desde aca
if($tempA==0 && $fobSubt==0){}//se comprueba que es el primer valor de los registros y no imprime nada
 else  if($PartidaAgrupada!=$tempB || $FlagAgrupado!=$tempA){//compara si son diferentes las banderas de agrupados asi para poder Agrupar
            $varr.="<tr>
<td style=\"text-align:left\"><b>".$tempB."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>DAI:". $dai ."</b></td>
                <td style=\"text-align:right\"><b>". number_format(round($bultosSubt,2),2) ."</b></td>                    
                <td style=\"text-align:right\"><b>". number_format(round($pesoSubt,2),2) ."</b></td>
		<td style=\"text-align:right\"><b>". number_format(round($cuantiaSubt,2),2) ."</b></td>
                <td style=\"text-align:right\"><b>". number_format(round($fobSubt,2),2) ."</b></td>
                <td colspan=\"3\" style=\"text-align:center\"><b>Item: &nbsp;&nbsp;". $NumItem ."</b></td>
		</tr><tr><td colspan=8></td></tr>";
                //CALCULO DE LOS TOTALES Y SUBTOTALES
                $fobTotal+=$fobSubt;
		$fobSubt=0;
                //------//
                $bultosTotal+=$bultosSubt;
		$bultosSubt=0;
                //------//
                $pesoTotal+=$pesoSubt;
		$pesoSubt=0;
                //------//
                $cuantiaTotal+=$cuantiaSubt;
		$cuantiaSubt=0;
                //-----------------------------//
                $NumItem++;
                
             //}
	} //hasta aca es la agrupacion, se hace antes porque para el primer registro no hay ninguna agrupacion
 
 $varr.="<tr>
		<td >".htmlentities($row_exp[0])."</td>
		<td style=\"text-align:right\">$row_exp[1]</td>
		<td style=\"text-align:right\">$row_exp[2]</td>
		<td style=\"text-align:right\">$row_exp[3]</td>
		<td style=\"text-align:right\">".number_format(round($row_exp[4],2),2)."</td>
		<td style=\"text-align:right\">$row_exp[5]</td>
                <td style=\"text-align:right\">$row_exp[6]</td>
                <td style=\"text-align:center\">$row_exp[10]</td>
                </tr>";

	
$bultosSubt+=$row_exp[1];
$pesoSubt+=$row_exp[2];
$cuantiaSubt+=$row_exp[3];
$fobSubt+=$row_exp[4];


$tempA=$row_exp[7];//Guarda un temporal que seria la bandera de agrupado anterior para comparar
$tempB=$row_exp[8];//Guarda un temporal que seria la partida arancelaria anterior para comparar


}//FIN IMPRESION CADA REGISTRO
$varr.="<tr>
		<td><b>".$tempB."</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <b>DAI:". $dai ."</b></td>
                <td style=\"text-align:right\"><b>". number_format(round($bultosSubt,2),2) ."</b></td>                    
                <td style=\"text-align:right\"><b>". number_format(round($pesoSubt,2),2) ."</b></td>
		<td style=\"text-align:right\"><b>". number_format(round($cuantiaSubt,2),2) ."</b></td>
                <td style=\"text-align:right\"><b>". number_format(round($fobSubt,2),2) ."</b></td>
                <td colspan=\"3\" style=\"text-align:center\"><b>Item: &nbsp;&nbsp;". $NumItem ."</b></td>
		</tr><tr><td colspan=8></td></tr></table>";

$fobTotal+=$fobSubt;
$bultosTotal+=$bultosSubt;
$pesoTotal+=$pesoSubt;
$cuantiaTotal+=$cuantiaSubt;

$fobSubt=0;
$bultosSubt=0;
$pesoSubt=0;
$cuantiaSubt=0;

$DAI="0.00";
//-------ENCABEZADO DEL REPORTE CALCULADO AL FINAL DE LOS TOTALES----------//
$result=mysql_query("select r.*,e.nombre from retaceo r inner join empresas e on r.nit=e.nit where r.idRetaceo='".hideunlock($_SESSION["n_declaracion"])."'",$link);

while($rows_e = mysql_fetch_array($result)){ //CONSULTA PARA ENCABEZADO
$pdf->addpage($orientacion,'legal');//AGREGA NUEVA PAGINA POR CADA MES

$rsd='
<table width="100%">
<tr><td colspan="3" style="text-align:center"><b>REPORTE PARTIDAS ARANCELARIAS AGRUPADAS</b><br></td></tr>
<tr>
	<td width="100px"><b>No. Retaceo:</b> </td>
        <td width="225px" style="text-align:right">'.$rows_e["numRegistro"].'&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="100px"><b>FOB Total:</b></td> 
        <td width="100px" style="text-align:right">'.number_format($rows_e["FOB"],2).'&nbsp;&nbsp;&nbsp;&nbsp;</td> 
        <td width="100px"><b>DAI Total:</b> </td> 
        <td width="60px" style="text-align:right">'.$DAI.'</td>     
</tr>
<tr>
	<td width="100px"><b>NIT:</b> </td>
        <td width="225px" style="text-align:right">'.$rows_e["NIT"].'&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="100px"><b>Flete Total:</b></td> 
        <td width="100px" style="text-align:right">'.number_format($rows_e["flete"],2).'&nbsp;&nbsp;&nbsp;&nbsp;</td> 
        <td width="100px"><b>IVA Total:</b> </td> 
        <td width="60px" style="text-align:right">'.$rows_e["IVA"].'</td>     
</tr>
<tr>
	<td width="100px"><b>Fecha:</b> </td>
        <td width="225px" style="text-align:right">'.date("d-m-Y", strtotime($rows_e["fecha"])).'&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="100px"><b>O.Gastos:</b></td> 
        <td width="100px" style="text-align:right">'.$rows_e["otrosGastos"].'&nbsp;&nbsp;&nbsp;&nbsp;</td> 
        <td width="100px"><b>Total A Pagar:</b> </td> 
        <td width="60px" style="text-align:right">'.$rows_e["aPago"].'</td>     
</tr>
<tr>
	<td width="100px"><b>Consignatario:</b> </td>
        <td width="225px" style="text-align:left">'.$rows_e["nombre"].'&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="100px"><b>Seguro:</b></td> 
        <td width="100px" style="text-align:right">'.number_format($rows_e["seguro"],2).'&nbsp;&nbsp;&nbsp;&nbsp;</td> 
        <td colspan="2"></td> 
</tr>
<tr>
	<td width="100px"><b>Doc.Transporte:</b> </td>
        <td width="225px" style="text-align:right">'.$rows_e["numeroDocumentoTransporte"].'&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td width="100px"><b>CIF Total:</b></td> 
        <td width="100px" style="text-align:right">'.number_format($rows_e["CIF"],2).'&nbsp;&nbsp;&nbsp;&nbsp;</td> 
        <td colspan="2"></td>   
</tr>

</table>	
<br>


<table border="0" width="100%" cellpadding="1" cellspacing="0" >
<tr>	
		<td style="width:255px" ><b>Descripcion</b></td>
		<td style="text-align:right;width:55px"><b>Bultos</b></td>
		<td style="text-align:right;width:80px"><b>Peso Bruto</b></td>
		<td style="text-align:right;width:60px"><b>Cuantia</b></td>
		<td style="text-align:right;width:80px"><b>FOB</b></td>
		<td style="text-align:right;width:65px"><b>Factura</b></td>
                <td style="text-align:right;width:30px"><b>ODF</b></td>
                <td style="text-align:center;width:80px"><b>TLC</b></td>
</tr>';
}
//------------FIN ENCABEZADO---------------//


// ---------------PIE DEL REPORTE-----------------//
//PIE DE TABLA
$fin=$rsd.$varr."<b>
<table border=\"0\" >              
        <tr>
        <td>&nbsp;</td>
        <td colspan=\"2\" style=\"text-align:center\">Bultos</td>
        <td colspan=\"2\" style=\"text-align:center\">Peso (Kgs)</td>
        <td colspan=\"2\" style=\"text-align:center\">Cuantia</td>
        <td colspan=\"2\" style=\"text-align:center\">FOB Total</td>
        <td>&nbsp;</td>
        </tr>
        <tr>
        <td>&nbsp;</td>
        <td colspan=\"2\" style=\"text-align:center\">".number_format(round($bultosTotal,2),2)."</td>
        <td colspan=\"2\" style=\"text-align:center\">".number_format(round($pesoTotal,2),2)."</td>
        <td colspan=\"2\" style=\"text-align:center\">".number_format(round($cuantiaTotal,2),2)."</td>
        <td colspan=\"2\" style=\"text-align:center\">".number_format(round($fobTotal,2),2)."</td>
        <td>&nbsp;</td>
        </tr>
</table></b>
<br><br>
";

$pdf->writeHTML($fin, true, false, false, false, '');




/////////////////////////////////////////////////////////////////////
   
$db->desconectar();	 
//Close and output PDF document
$pdf->Output('reporte.pdf', 'I');
}

?>

