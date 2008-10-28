<?PHP
assert( $angemeldet ) or exit();

$editable = hat_dienst(4,5);
get_http_var( 'ro', 'u', 0, true );
if( $ro or $readonly )
  $editable = false;

$msg = '';
$problems = '';

get_http_var( 'lieferanten_id', 'u', 0, true );
if( $lieferanten_id ) {
  $row = sql_getLieferant( $lieferanten_id );
} else {
  $row = false;
}
get_http_var('name','H',$row);
get_http_var('adresse','H',$row);
get_http_var('ansprechpartner','H',$row);
get_http_var('telefon','H',$row);
get_http_var('fax','H',$row);
get_http_var('mail','H',$row);
get_http_var('liefertage','H',$row);
get_http_var('bestellmodalitaeten','H',$row);
get_http_var('kundennummer','H',$row);
get_http_var('url','H',$row);

get_http_var( 'action', 'w', '' );
$editable or $action = '';
if( $action == 'save' ) {
  $values = array(
    'name' => $name
  , 'adresse' => $adresse
  , 'ansprechpartner' => $ansprechpartner
  , 'telefon' => $telefon
  , 'fax' => $fax
  , 'mail' => $mail
  , 'liefertage' => $liefertage
  , 'bestellmodalitaeten' => $bestellmodalitaeten
  , 'kundennummer' => $kundennummer
  , 'url' => $url
  );
  if( ! $name ) {
    $problems = $problems . "<div class='warn'>Kein Name eingegeben!</div>";
  } else {
    if( $lieferanten_id ) {
      if( sql_update( 'lieferanten', $lieferanten_id, $values ) ) {
        $msg = $msg . "<div class='ok'>&Auml;nderungen gespeichert</div>";
      } else {
        $problems = $problems . "<div class='warn'>Änderung fehlgeschlagen: " . mysql_error() . '</div>';
      }
    } else {
      if( ( $lieferanten_id = sql_insert( 'lieferanten', $values ) ) ) {
        $self_fields['lieferanten_id'] = $lieferanten_id;
        $msg = $msg . "<div class='ok'>Lieferant erfolgreich angelegt:</div>";
      } else {
        $problems = $problems . "<div class='warn'>Eintrag fehlgeschlagen: " .  mysql_error() . "</div>";
      }
    }
  }
}

open_form( 'small_form', '', 'action=save' );
  open_fieldset( 'small_form', "style='width:470px;'", ( $lieferanten_id ? 'Stammdaten Lieferant' : 'Neuer Lieferant' ) );
    echo $msg . $problems;
    open_table('small_form');
      form_row_text( 'Name:', ( $editable ? 'name' : false ), 50, $name );
      form_row_text( 'AnsprechpartnerIn:', ( $editable ? 'ansprechpartner' : false ), 50, $ansprechpartner );
      form_row_text( 'Telefonnummer:', ( $editable ? 'telefon' : false ), 50, $telefon );
      form_row_text( 'Faxnummer:', ( $editable ? 'dax' : false ), 50, $fax );
      form_row_text( 'Email:', ( $editable ? 'mail' : false ), 50, $mail );
      form_row_text( 'Liefertage:', ( $editable ? 'liefertage' : false ), 50, $liefertage );
      form_row_text( 'Bestellmodalit&auml;ten:', ( $editable ? 'bestellmodalitaeten' : false ), 50, $bestellmodalitaeten );
      form_row_text( 'Kundennummer:', ( $editable ? 'kundennummer' : false ), 50, $kundennummer );
      form_row_text( 'Webadresse:', ( $editable ? 'url' : false ), 50, $url );
      open_tr();
        open_td( 'right', "colspan='2'" );
          if( $lieferanten_id > 0 ) {
            echo fc_link( 'lieferantenkonto', "lieferanten_id=$lieferanten_id,text=Lieferantenkonto..." );
          }
          open_span( 'qquad' );
          if( $editable )
            submission_button();
          else
            close_button();
          close_span();
    close_table();
  close_fieldset();
close_form();

?>
