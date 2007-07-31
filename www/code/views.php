<?php
//This file defines views for foodsoft data
/**
 *  Zeigt ein Produkt als Bestellungsübersicht
 */
function areas_in_menu($area){
 ?>
   <tr>
       <td><input type="button" value="<? echo $area['title']?>" class="bigbutton" onClick="self.location.href='<? echo $area['area']?>'"></td>
	<td valign="middle" class="smalfont"><? echo $area['hint']?></td>
    </tr> 		 
  <?
}
function areas_in_head($area){

?>
  <li>
  <a href="<? echo $area['area']?>" class="first" title="<? echo $area['hint']?>"><? echo $area['title']?></a> </li>
<?
}
function products_overview($bestell_id, $editAmounts = FALSE, $editPrice = FALSE){
      $result1 = sql_bestellprodukte($bestell_id);
      $preis_summe = 0;
     ?>
      <form action="index.php" method="post">
      <table>
               <tr>
                   <th>Bezeichnung</th>
                   <th>Produktgruppe</th>
                   <th>Bestellnummer</th>
                   <th>Gebinde Einheit</th>
                   <th>Menge</th>
                   <th>Einheitspreis: <small>Netto (Brutto, MWST, Pfand)</small> </th>
                   <th>Gesamtpreis</th>
               </tr>

     <?

      while  ($produkte_row = mysql_fetch_array($result1)) {
      	 $produkt_id =$produkte_row['produkt_id'];
	 if($produkte_row['liefermenge']!=0){	
		  ?>
	       <tr>
	       <td valign="top"><b><?echo( $produkte_row['produkt_name']);?></b></td>
               <td valign="top"><?PHP echo $produkte_row['produktgruppen_name']; ?></td>
               <td><? echo($produkte_row['gebindegroesse'])?></td>
               <td><? echo($produkte_row['gebindegroesse']." * ".$produkte_row['einheit'])?></td>
	       <?
			$liefer = $produkte_row['liefermenge'];
			$fieldname = "verteilmenge".$produkt_id;
	       
	       if($editAmounts){ ?>
               		<td> <input name="<? echo($fieldname) ?>" type="text" size="3" value="<? echo($liefer) ?>"/></td>
	       <?  } else { ?>
               		<td> <? echo($liefer) ?></td>
	       <?  } 
	       if($editPrice){ ?>
	       		<td> <?  $preis= preis_selection($produkt_id, $produkte_row['produktpreise_id']); ?> </td>
	       <?  } else { ?>
	       		<td> <?  $preis=  $produkte_row['preis'];
				preis_view($produkte_row);
			?> </td>
	       <?  } ?>
		     <td> <?echo($preis*$liefer ); $preis_summe+=$preis*$liefer ?> </td>
		  </tr>

     		<tr style='border:none'>
		 <td colspan='4' style='border:none'></td>
	      </tr>
   
   <?
   } //end if ... reichen die bestellten mengen? dann weiter im text
   
} //end while produkte array            
?>
   <tr>
   <td align=right colspan="7" td>Summe <?echo($preis_summe)?></td></tr>
   <? if($editAmounts or $editPrice){?>
	   <tr style='border:none'>
		<td colspan='4' style='border:none'>
		   <input type="hidden" name="area" value="lieferschein">			
		   <input type="hidden" name="bestgr_pwd" value="<?PHP echo $bestgr_pwd; ?>">
		   <input type="hidden" name="bestellungs_id" value="<?PHP echo $bestell_id; ?>">
		   <input type="submit" value=" Lieferschein ändern ">
		   <input type="reset" value=" Änderungen zurücknehmen">
		</td>
	   </tr>
   <?}?>
   </table>                   
   </form>
      
<?

}
/**
 * Gibt einen einzelnen Preis mit Pfand und Mehrwertsteuer aus
 * Argument: mysql_fetch_array(sql_produktpreise2())
 */

function preis_view($pr){
        printf( "%5.2lf", ( $pr['preis'] - $pr['pfand'] ) / ( 1.0 + $pr['mwst'] / 100.0 ) );
				echo "&nbsp;({$pr['preis']},{$pr['mwst']},{$pr['pfand']})";

}

/**
 * Erzeugt eine Auswahl für alle Preise eines Produktes
 */
function preis_selection($produkt_id, $current_preis_id){
	$selectpreis = "preis".$produkt_id;

		?>
                <select name="<? echo($selectpreis)?>"> 
	       		<?
			   $preise=sql_produktpreise2($produkt_id);
			   while($pr = mysql_fetch_array($preise)){
				$sel = "";
			   	if($pr['id']==$current_preis_id ){
					$sel = " selected=\"selected\"";
					$preis =$pr['preis'];
				}
				echo "<option value='{$pr['id']}' $sel>";
				preis_view($pr);
        			echo "</option>\n";

			   }
	       
	       		?>
	   	     </select>
		     <?
		     return $preis;
}


/**
 * Liste zur Auswahl einer Bestellung via Link
 */
function select_bestellung_view($result, $area, $head="Bitte eine Bestellung wählen:" ){

      echo "<h1>".$head."</h1>";
      $span =  count($area);
      ?>
      <br /> <br />
	     <table style="width:600px;" class="liste">
		  <tr>
		    <th>Name</th>
		    <th>Beginn</th>
		    <th>Ende</th>
		    <th colspan=<?echo $span?>></th>
		 </tr>
		 <?php
		 while ($row = mysql_fetch_array($result)) {
		 ?>
		 <tr>                                 
		    <td><?echo $row['name']?></td>
		    <td><? echo $row['bestellstart']; ?></td>
		    <td><? echo $row['bestellende']; ?></td>
		    <?
			while($area_name = current($area)){
			    $label=key($area);
				   ?>
				   <td>
				      <form action="index.php" method="POST">         
				      <input type="hidden" name="bestellungs_id" value=<? echo($row['id'])?> >
				      <input type="hidden" name="area" value=<? echo($area_name)?> >
					  <input type="submit" value="<?echo($label)?>">
				       </form>
				   </td>
		      <?
		            next($area);

			}
			reset($area);
		    ?>
		 </tr>   
		  <?  }?>

            </table> 

<?
  
}

function select_products_not_in_list($bestell_id){
	   echo "Produkt: <select name=\"produkt_id\"> ";
	 if($bestell_id!=0){
	   $produkte=getProdukteVonLieferant(getProduzentBestellID($bestell_id), $bestell_id);
	   while($prod = mysql_fetch_array($produkte)){
		echo "<option value=\"".$prod['p_id']."\">".
			$prod['name']." (".$prod['einheit'].") "."</option>\n";
	   }
	 }
	 echo "  </select>\n";

}
function distribution_tabellenkopf($name){
  ?>
            <tr class="legende">
               <th><?echo $name?></th>
               <th colspan='2'>bestellt (toleranz)</th>
               <th colspan='2'>geliefert</th>
               <th colspan='2'>Einzelpreis</th>
               <th>Gesamtpreis</th>
            </tr>
 
  <?
}
function distribution_view($name, $festmenge, $toleranz, $verteilmenge, $verteilmult, $verteileinheit, $preis, $inputbox_name = false){
  echo "
    <tr>
      <td>$name</td>
      <td class='mult'><b>" . $festmenge * $verteilmult . " </b> (" . $toleranz * $verteilmult . ")</td>
      <td class='unit'>$verteileinheit</td>
      <td class='mult'>
  ";
  if($inputbox_name===false){
      echo $verteilmenge * $verteilmult;
  }else{
      echo "<input name='$inputbox_name' type='text' size='5'
            value='" . $verteilmenge * $verteilmult . "' />";
  }
  echo "
      </td>
      <td class='unit'>$verteileinheit</td> 
      <td class='mult'>$preis</td>
      <td class='unit'>/ $verteilmult $verteileinheit</td>
      <td class='number'>" . sprintf( "%8.2lf", $verteilmenge * $preis ) . " </td>
    </tr>
  ";
}

function sum_row($sum){
?>
<tr style='border:none'>
		 <td colspan='7' style='border:none' align=right><b>Summe:</b></td>
     <td class='number'><b><?echo
     sprintf( "%8.2lf", $sum); ?></b></td>
	      </tr>
<?
}
function bestellung_overview($row){
	 ?>
         <table class="info">
               <tr>
                   <th> Bestellung: </th>
                     <td style="font-size:1.2em;font-weight:bold"><?PHP echo $row['name']; ?></td>
                </tr>
               <tr>
                   <th> Bestellbeginn: </th>
                     <td><?PHP echo $row['bestellstart']; ?></td>
                </tr>
               <tr>
                   <th> Bestellende: </th>
                     <td><?PHP echo $row['bestellende']; ?></td>
                </tr>                
            </table>
	    <br/>
	    <?
}

?>
