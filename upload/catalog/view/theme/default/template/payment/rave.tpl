<?php if (!$livemode) { ?>
<div class="warning"><?php echo $text_testmode; ?></div>
<?php } ?>

<script src="<?php echo $script; ?>"></script>
  
<form>
  <button type="button" style="cursor:pointer;" value="Pay Now"  class="btn btn-primary" id="submitRave">Pay to complete your order</button>
</form>

<script>
  document.getElementById("submitRave").addEventListener("click", function(e){
    getpaidSetup({
      PBFPubKey: '<?php echo $public_key; ?>',
      customer_email: '<?php echo $email; ?>',
      customer_firstname:'<?php echo $firstname; ?>',
      customer_lastname: '<?php echo $lastname; ?>',
      amount: <?php echo $amount; ?>,
      customer_phone: '<?php echo $phone; ?>',
      country: '<?php echo $country; ?>',
      currency: '<?php echo $currency; ?>',
      txref: '<?php echo $reference; ?>',
      onclose: function() {},
      callback: function(response) {
        var flw_ref = response.tx.flwRef; 
        console.log(response);
        if (response.tx.chargeResponseCode == "00" || response.tx.chargeResponseCode == "0" ) {
           var callback_url = '<?php echo $callback_url; ?>';
                var decoded = callback_url.replace(/&amp;/g, '&');
                decoded = decoded + '&flw_reference='+flw_ref;
                  window.location.href=decoded;
        
        } else {
           alert(response.respmsg);
        }
      }
    });
  });




</script>