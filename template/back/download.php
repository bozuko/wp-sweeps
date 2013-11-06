<div class="wrap">
  <h2>Download Entries for <strong><?= $campaign->post_title ?></strong></h2>
  
  <?php if( @$_GET['error'] ){ ?>
  <div class="error">
    <p><?= $_GET['error'] ?></p>
  </div>
  <?php } ?>
  
  <form enctype="multipart/form-data" action="?" method="post">
    <input type="hidden" name="sweep_download_entries" value="<?= $campaign->ID ?>" />
    <p>Please provide your private key below.</p>
    <input type="file" name="private_key" /> <input type="submit" class="button button-primary" value="Download"/>
  </form>

</div>