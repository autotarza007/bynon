<?php include('server/server.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.8/dist/sweetalert2.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="css/style.css" rel="stylesheet">
    <?php 
        if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
            include('admin/headeradmin_out.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        } elseif (isset($_SESSION['success']) && $_SESSION['success'] === true) {
            include('header1.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        } else {
            include('header.php'); // ตรวจสอบเส้นทางให้ถูกต้อง
        }
    ?>
</head>
<body class="background">
<div class="container col-xl-13 col-xxl-9 px-4 py-5">
    <div class="row align-items-center g-lg-5 py-5">
      <div class="col-lg-7 text-center text-lg-start">
        <img src="logo.png" alt="Logo" class="img-fluid mx-auto d-block" style="max-width: 100%; height: auto;">
        <h1 class="display-4 fw-bold lh-1 text-body-emphasis mb-3 text-white">Vertically centered hero Register form</h1>
        <p class="col-lg-12 fs-4 text-white">Below is an example form built entirely with Bootstrap’s form controls. Each required form group has a validation state that can be triggered by attempting to submit the form without completing it.</p>
      </div>
      <div class="col-md-10 mx-auto col-lg-5">
        <form id="registerForm" class="p-4 p-md-5 border rounded-3 bg-body-tertiary custom-background">
          <div class="form-floating mb-3">
            <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="gmail" required>
            <label for="floatingInput" class="text-white">Email address</label>
          </div>
          <div class="form-floating mb-3">
            <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password" required>
            <label for="floatingPassword" class="text-white">Password</label>
          </div>
          <div>
            <div class="g-recaptcha" data-sitekey="6Ld4USEqAAAAADRk88XlAfikPr0459lHsh8YPktm"></div>
          </div>
          <div class="checkbox mb-3">
          </div>
          <button type="submit" class="w-100 btn btn-lg btn-primary">Register</button>
          <hr class="my-4">
          <small class="text-body-secondary text-white">By clicking Sign up, you agree to the terms of use.</small>
        </form>
      </div>
    </div>
</div>

<?php include('footer.php'); ?>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.3.8/dist/sweetalert2.all.min.js"></script>

<script>
$(document).ready(function() {
    $('#registerForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: 'backend/register_db.php',
            type: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                let data = JSON.parse(response);
                if(data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message,
                        icon: 'success',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'index.php';
                        }
                    });
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonText: 'Try Again'
                    });
                }
            },
            error: function() {
                Swal.fire({
                    title: 'Error!',
                    text: 'Something went wrong!',
                    icon: 'error',
                    confirmButtonText: 'Try Again'
                });
            }
        });
    });
});
</script>
</body>
</html>
