<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Site</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="logo.png">
    <link href="css/login.css" rel="stylesheet">
    <link href="css/bg.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
      .g-recaptcha {
        transform: scale(0.90); /* ปรับขนาดตามต้องการ */
        transform-origin: 0 0;
        margin: 0 auto; /* จัดให้อยู่กลาง */
      }
    </style>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>
<body class="background">
<?php include('header.php'); ?>
<main class="form-signin w-100 m-auto">
  <form id="loginForm" class="custom-background mt-5">
    <img class="mb-4" src="logo.png" alt="" width="72" height="57">
    <h1 class="h3 mb-3 fw-normal text-white">Please login</h1>

    <div class="form-floating">
      <input type="email" class="form-control" id="floatingInput" placeholder="name@example.com" name="gmail" required>
      <label for="floatingInput" class="text-white">Email address</label>
    </div>
    <div class="form-floating">
      <input type="password" class="form-control" id="floatingPassword" placeholder="Password" name="password" required>
      <label for="floatingPassword" class="text-white">Password</label>
    </div>

    <!-- reCAPTCHA -->
    <div class="ml-6">
      <div class="g-recaptcha" data-sitekey="6Ld4USEqAAAAADRk88XlAfikPr0459lHsh8YPktm"></div>
    </div>
    <!-- reCAPTCHA -->

    <button class="btn btn-primary w-100 py-2 text-white" type="submit">Sign in</button>
  </form>
</main>

<?php include('footer.php'); ?>

<script>
  $(document).ready(function() {
    $('#loginForm').on('submit', function(e) {
      e.preventDefault();
      $.ajax({
        url: 'backend/login_db.php',
        type: 'POST',
        data: $(this).serialize(),
        success: function(response) {
          var result = JSON.parse(response);
          if (result.status === 'success') {
            Swal.fire({
              icon: 'success',
              title: 'ล็อกอินสำเร็จ!',
              text: result.message,
              confirmButtonText: 'ตกลง'
            }).then(() => {
              window.location.href = 'index.php'; // ตรวจสอบ URL ให้ถูกต้อง
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'เกิดข้อผิดพลาด!',
              text: result.message,
              confirmButtonText: 'ตกลง'
            });
          }
        },
        error: function(xhr, status, error) {
          Swal.fire({
            icon: 'error',
            title: 'เกิดข้อผิดพลาด!',
            text: 'การเชื่อมต่อมีปัญหา กรุณาลองใหม่อีกครั้ง',
            confirmButtonText: 'ตกลง'
          });
        }
      });
    });
  });
</script>

</body>
</html>
