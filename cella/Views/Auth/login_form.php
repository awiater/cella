<?= $this->extend($_loginPage); ?>
<?= $this->section('body') ?>
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg"><?= lang('system.auth.loginform_title') ?></p>

      <?= form_open(current_url(),['id'=>'id_loginform'],$form_hidden); ?>
        <div class="input-group mb-3">
          <input type="text" class="form-control" placeholder="<?= lang('system.auth.loginform_email') ?>" name="login">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="<?= lang('system.auth.loginform_password') ?>" name="password">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-8">
			<a href="<?= $url_forget ?>"><?= lang('system.auth.loginform_forgot') ?></a>
          </div>
          <!-- /.col -->
          <div class="col-4">
            <button type="button" class="btn btn-primary btn-block" id="id_loginform_submit"><?= lang('system.auth.loginform_sign_in') ?></button>
          </div>
          <!-- /.col -->
        </div>
      </form>
    </div>
    <!-- /.login-card-body -->
  </div>
  <script>
  	$("#id_loginform_submit").on("click",function(){
  		$("#id_loginform").submit();
  	});
  </script>
<?= $this->endSection() ?>
