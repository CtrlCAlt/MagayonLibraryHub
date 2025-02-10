<?php session_start(); ?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <style>
        /* Background Color */
        body {
            background-color: #fceff9; /* Light pastel pink */
            font-family: 'Arial', sans-serif;
        }
        
        /* Card Styling */
        .card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color:rgb(212, 150, 14); /* Light pastel purple */
            color:rgb(11, 12, 12); /* Dark blue text for contrast */
            font-weight: bold;
            border-bottom: none;
            text-align: center;
        }

        /* Form Input Styling */
        .form-control {
            background-color: rgb(232, 232, 245); /* Light pastel purple */
            border-radius: 8px;
            border: 1px solid #e0c3fc; /* Soft purple border */
        }

        .form-control:focus {
            border-color: rgb(83, 49, 5); /* Soft blue */
            box-shadow: 0 0 5px rgba(223, 148, 35, 0.5);
        }

        /* Submit Button */
        .btn-custom {
            background-color: rgb(224, 70, 9); /* Pastel purple */
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            font-size: 1rem;
            color: white;
            transition: 0.3s;
        }

        .btn-custom:hover {
            background-color: rgb(224, 185, 10); /* Darker shade */
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h4>📩 MAGAYON LIBRARY HUB</h4>
                </div>
                <div class="card-body">
                    <form action="sendmail.php" method="POST">
                        <div class="mb-3">
                            <label for="fullname">Full Name</label>
                            <input type="text" name="full_name" id="fullname" required class="form-control">
                        </div>
                        
                        <div class="mb-3">
                            <label for="email_address">Email Address</label>
                            <input type="email" name="email" id="email_address" required class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="subject">Subject</label>
                            <input type="text" name="subject" id="subject" required class="form-control">
                        </div>

                        <div class="mb-3">
                            <label for="message">Message</label>
                            <textarea name="message" id="message" required class="form-control" rows="3"></textarea>
                        </div>

                        <div class="text-center">
                            <button type="submit" name="submitContact" class="btn btn-custom"> Submit Mail</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php if (isset($_SESSION['status'])): ?>
                <div class="alert alert-success mt-3 text-center">
                    <?= $_SESSION['status']; unset($_SESSION['status']); ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    var messageText = "<?= $_SESSION['status'] ?? ''; ?>";
    if (messageText !== '') {
        Swal.fire({
            title: "Thank You!",
            text: messageText,
            icon: "success"
        });
    }
</script>

</body>
</html>
