<?php
include 'conexao.php';
require './vendor/autoload.php';

header('Content-Type: application/json; charset=utf-8');
$request = json_decode(file_get_contents('php://input')); 

$placa = str_replace("-", "", $request->placa);
$dia = $request->dia;$mes = $request->mes; $ano = $request->ano;
$data = $dia.'/'.$mes.'/'.$ano;
$datasave = $dia.'_'.$mes.'_'.$ano;

$sql = "SELECT placa, TIME(LOCAL) as local, velocidade, pos_id, substring(observacao, 1, 25) AS observacao
from sau_posicionamento WHERE day(local) = $dia and month(local) = $mes AND YEAR(local) = $ano
AND placa = '$placa' group by local ORDER BY local DESC";

$query = mysqli_query($conexao,$sql);


$dados = "<!DOCTYPE html>";
$dados .= "<html lang='pt-br'>";
$dados .= "<head>";
$dados .= "<meta charset='UTF-8'>";
$dados .= "<link rel='stylesheet' href='http://localhost/requisicao/css/style.css'";
$dados .= "<title>Gerar PDF</title>";
$dados .= "</head>";
$dados .= "<body>";
$dados .= "<div align=center>";
$dados .= "<table cellpadding=0 cellspacing=0 width=735 style='border-collapse:collapse;table-layout:fixed;width:551pt'>";
$dados .= "<col width=88 style='width:66pt'>";
$dados .= "<col width=157 style='width:118pt'>";
$dados .= "<col width=87 style='width:65pt'>";
$dados .= "<col width=152 style='width:114pt'>";
$dados .= "<col width=164 style='width:123pt'>";
$dados .= "<col width=87 style='width:65pt'>";
$dados .= "<tr height=32 style='height:24pt'>";
$dados .= "<td colspan=5 rowspan=2 height=53 width=648 style='border-right:.5pt solid black; height:40.0pt;width:486pt'>														
<span style='position:absolute;z-index:1;margin-left:530px;margin-top:52px;width:116px;height:104px'>";
$dados .= "</span>";
$dados .= "<center><img src='http://localhost/requisicao/imgs/iconVet.jpg'></center>";
$dados .= "<table cellpadding=0 cellspacing=0>
<tr>
    <td colspan=5 rowspan=2 height=53 class=xl67 width=800 style='border-right:.5pt solid black;height:40.0pt;width:486pt'>
        Relatório de posições
    </td></tr>
</table>
</tr>
<tr height=21 style='height:16.0pt'></tr>



<td colspan=5 height=27 class=xl80 style='border-right:.5pt solid black;height:20.0pt'>&nbsp;</td><td></td>

<tr height=27 style='height:20.0pt'>
    <td height=27 class=xl73 style='height:20.0pt;border-top:none'>Placa:</td>
    <td class=placa>$placa</td>
    <td class=xl73>Data:</td>
    <td class=data>$data</td>
</tr>

<tr height=8 style='height:6.0pt'>
   <td colspan=5 height=27 class=xl80 style='border-right:.5pt solid black;height:20.0pt'>&nbsp;</td><td></td>
</tr>
<tr height=27 style='height:20.0pt'>
    <td colspan=5 height=27 class=xl71 style='border-right:.5pt solid black;height:20.0pt'>Informações</td><td></td>
</tr>
<tr height=21 style='height:16.0pt'>
    <td rowspan=2 class=xl83 style='border-top:none'>Horário</td>
    <td rowspan=2 class=xl83 style='border-top:none'>Localização</td>
    <td rowspan=2 class=xl83 style='border-top:none'></td>
    </td>
    <td rowspan=2 class=xl84 width=152 style='border-top:none;width:114pt'>Velocidade do Veículo</td>
    <td rowspan=2 class=xl82 width=164 style='border-top:none;width:123pt'>Velocidade Máxima da Via</td>
</tr>
<tr height=21 style='height:16.0pt'></tr>";

while($array = mysqli_fetch_assoc($query)){
      $dados .= '<tr height=21>
        <td height=21 class=xl65 style="height:16.0pt;border-top:none">'.$array['local'].'</td>
        <td class=xl66>'.$array['observacao'].'...</td>
        <td></td>
        <td class=xl65>'.$array['velocidade'].'km/h</td>
        <td class=xl65>'.$array['pos_id'].'km/h</td>
        <td></td>
        </tr>';
}

use Dompdf\Dompdf;

$dompdf = new Dompdf(['enable_remote' => true]);
$dompdf->loadHtml($dados);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$fetch = "http://192.168.0.38/requisicao/$placa/$datasave.pdf";


if(is_dir("$placa")){
    echo json_encode(array('url'=>$fetch, 'dir'=>$placa.'_'.$datasave));
}else{
    mkdir("./$placa", 0755);
    echo json_encode(array('url'=>$fetch, 'dir'=>$placa.'_'.$datasave));
}

file_put_contents("$placa/$datasave.pdf", $dompdf->output());


 echo $dompdf->stream(); // habilitar caso não for usar como retorno de post  
