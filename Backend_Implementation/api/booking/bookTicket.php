<?php
// Start a session or resume the existing session
session_start();

// Include Composer's autoload file and the database connection file
require '../../../vendor/autoload.php'; // Adjust the path as necessary
include '../../partials/db_connect.php';

// Use PHPMailer classes for sending emails
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Check if the user is logged in, if not redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

// Get the logged-in user's ID from the session
$user_id = $_SESSION['user_id'];

// Check database connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Fetch user details
$sql_user = "SELECT username, email, phone_number FROM users WHERE id = $user_id";
$result_user = mysqli_query($conn, $sql_user);
$user = mysqli_fetch_assoc($result_user);

// Fetch all movies
$sql_all_movies = "SELECT id, title, price FROM movies";
$result_all_movies = mysqli_query($conn, $sql_all_movies);
$all_movies = mysqli_fetch_all($result_all_movies, MYSQLI_ASSOC);

// Fetch all cinemas
$sql_all_cinemas = "SELECT id, name FROM cinemas";
$result_all_cinemas = mysqli_query($conn, $sql_all_cinemas);
$all_cinemas = mysqli_fetch_all($result_all_cinemas, MYSQLI_ASSOC);

// Fetch movie and cinema details based on GET parameters or default to the first available movie/cinema
$movie_id = isset($_GET['movie_id']) ? intval($_GET['movie_id']) : (isset($all_movies[0]['id']) ? $all_movies[0]['id'] : 0);
$cinema_id = isset($_GET['cinema_id']) ? intval($_GET['cinema_id']) : (isset($all_cinemas[0]['id']) ? $all_cinemas[0]['id'] : 0);

$sql_movie = "SELECT title, price FROM movies WHERE id = $movie_id";
$result_movie = mysqli_query($conn, $sql_movie);
$movie = mysqli_fetch_assoc($result_movie);

$sql_cinema = "SELECT name FROM cinemas WHERE id = $cinema_id";
$result_cinema = mysqli_query($conn, $sql_cinema);
$cinema = mysqli_fetch_assoc($result_cinema);

// Fetch showtimes for the selected movie and cinema
function fetchShowtimes($conn, $movie_id, $cinema_id) {
    $sql_showtimes = "SELECT showtimes FROM cinema_movies WHERE movie_id = $movie_id AND cinema_id = $cinema_id";
    $result_showtimes = mysqli_query($conn, $sql_showtimes);
    $showtimes = [];
    if ($result_showtimes) {
        while ($row = mysqli_fetch_assoc($result_showtimes)) {
            $showtimes = array_merge($showtimes, explode(',', $row['showtimes']));
        }
    }
    return $showtimes;
}

$showtimes = fetchShowtimes($conn, $movie_id, $cinema_id);

// Process form submission for buying tickets
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['buy_ticket'])) {
        $selected_date = $_POST['date'];
        $selected_time = $_POST['time'];
        $ticket_amount = intval($_POST['ticket_amount']);
        $total_price = $ticket_amount * $movie['price'];

        // Insert booking into the database
        $sql_booking = "INSERT INTO bookings (user_id, movie_id, booking_date, status) VALUES ($user_id, $movie_id, NOW(), 'Confirmed')";
        if (mysqli_query($conn, $sql_booking)) {
            $booking_id = mysqli_insert_id($conn);

            // Insert each ticket seat into the booking_seats table
            $seats = [];
            for ($i = 0; $i < $ticket_amount; $i++) {
                $seat_number = $_POST["seat_number_$i"];
                $seats[] = $seat_number;
                $sql_seat = "INSERT INTO booking_seats (booking_id, seat_number, date, time) VALUES ($booking_id, '$seat_number', '$selected_date', '$selected_time')";
                if (!mysqli_query($conn, $sql_seat)) {
                    echo "Error inserting seat: " . mysqli_error($conn);
                    exit;
                }
            }

            // Store booking details in session for later use
            $_SESSION['booking_details'] = [
                'movie' => $movie['title'],
                'cinema' => $cinema['name'],
                'time' => $selected_time,
                'date' => $selected_date,
                'seats' => $seats,
                'total_price' => $total_price,
            ];

            // Send booking confirmation email using PHPMailer
            $mail = new PHPMailer(true);
            try {
                // SMTP server settings
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'sba23313@student.cct.ie';
                $mail->Password = 'ehtorovkgupwhtmf';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Recipient settings
                $mail->setFrom('no-reply@yourdomain.com', 'CineSphere');
                $mail->addAddress($user['email'], $user['username']);

                // Email content
                $mail->isHTML(true);
                $mail->Subject = 'Your Ticket Booking Confirmation';
                $mail->Body = 'Dear ' . htmlspecialchars($user['username']) . ',<br><br>'
                    . 'Thank you for booking tickets with CineSphere!<br><br>'
                    . 'Movie: ' . htmlspecialchars($movie['title']) . '<br>'
                    . 'Cinema: ' . htmlspecialchars($cinema['name']) . '<br>'
                    . 'Date: ' . htmlspecialchars($selected_date) . '<br>'
                    . 'Time: ' . htmlspecialchars($selected_time) . '<br>'
                    . 'Seats: ' . implode(', ', array_map('htmlspecialchars', $seats)) . '<br>'
                    . 'Total Price: ' . number_format($total_price, 2) . ' EUR<br><br>'
                    . 'Enjoy your movie!<br>'
                    . 'CineSphere Team';

                // Send the email and store the status in session
                $mail->send();
                $_SESSION['email_status'] = 'Email has been sent successfully.';
                $_SESSION['email_status_type'] = 'success';
            } catch (Exception $e) {
                $_SESSION['email_status'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
                $_SESSION['email_status_type'] = 'error';
            }

            // Redirect to the same page to display the status message
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            // Error inserting booking
            echo "Error inserting booking: " . mysqli_error($conn);
            exit;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <title>Book Ticket - CineSphere</title>
    <style>
        body {
            background-color: #1a1a1a;
            color: #fff;
            font-family: Arial, Helvetica, sans-serif;
        }
        .navbar {
            background-color: #000;
            padding: 15px;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
        }
        .navbar-brand, .nav-link {
            color: #fff;
            font-size: 18px;
            font-weight: 700;
        }
        .container {
            margin-top: 80px;
        }
        .main-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .user-info, .booking-info, .seat-selection, .action-buttons {
            width: 100%;
            max-width: 600px;
            background-color: #333;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .section-title {
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .form-control, .btn {
            margin-bottom: 10px;
        }
        .available-seats {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }
        .available-seats div {
            width: 40px;
            height: 40px;
            background-color: #444;
            display: flex;
            justify-content: center;
            align-items: center;
            border-radius: 5px;
            cursor: pointer;
        }
        .available-seats div.selected {
            background-color: yellow;
            color: #444;
        }
        .available-seats div.over-limit {
            background-color: red;
            color: #fff;
        }
        .reset-btn, .logout-btn {
            background-color: red;
            color: white;
            margin-right: 10px;
        }
        .reset-btn:hover, .logout-btn:hover {
            background-color: #ff4c4c;
        }
        .total-price {
            font-size: 1.2em;
            font-weight: bold;
        }
        .btn-buy-ticket:disabled {
            background-color: gray;
            cursor: not-allowed;
        }
        .back-button {
            background-color: red;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: bold;
            text-align: center;
            text-decoration: none;
            margin-left: 10px;
        }
        .back-button:hover {
            background-color: #ff4c4c;
        }
        .alert-info, .alert-error {
            color: #fff;
            border: 1px solid #444;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .alert-info {
            background-color: #5cb85c;
        }
        .alert-error {
            background-color: #d9534f;
        }
        .alert-info .btn, .alert-error .btn {
            background-color: #007bff;
            border: none;
            color: #fff;
            padding: 5px 10px;
            border-radius: 3px;
        }
    </style>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const availableSeats = document.querySelectorAll('.available-seats div');
            const selectedSeatsContainer = document.getElementById('selected-seats-container');
            const ticketAmountInput = document.getElementById('ticket_amount');
            const totalPriceElement = document.getElementById('total_price');
            const movieSelect = document.querySelector('select[name="movie"]');
            const increaseTicketButton = document.getElementById('increaseTicket');
            const decreaseTicketButton = document.getElementById('decreaseTicket');
            const form = document.querySelector('form');
            const buyTicketButton = document.querySelector('.btn-buy-ticket');
            let selectedSeats = [];

            availableSeats.forEach(seat => {
                seat.addEventListener('click', function() {
                    const seatNumber = seat.getAttribute('data-seat-number');

                    if (seat.classList.contains('selected')) {
                        seat.classList.remove('selected');
                        selectedSeats = selectedSeats.filter(s => s !== seatNumber);
                    } else if (selectedSeats.length < parseInt(ticketAmountInput.value)) {
                        seat.classList.add('selected');
                        selectedSeats.push(seatNumber);
                    } else {
                        seat.classList.add('over-limit');
                        setTimeout(() => seat.classList.remove('over-limit'), 1000);
                    }

                    updateSelectedSeatsInput();
                    console.log(selectedSeats); // Debug statement
                });
            });

            function updateTotalPrice() {
                const ticketAmount = parseInt(ticketAmountInput.value);
                const selectedMovie = movieSelect.options[movieSelect.selectedIndex];
                const pricePerTicket = parseFloat(selectedMovie.getAttribute('data-price'));
                totalPriceElement.textContent = 'Total Price: ' + (ticketAmount * pricePerTicket).toFixed(2) + ' EUR';
            }

            function updateSeatSelection() {
                selectedSeats = [];
                availableSeats.forEach(seat => {
                    seat.classList.remove('selected');
                    seat.classList.remove('over-limit');
                });
            }

            ticketAmountInput.addEventListener('input', () => {
                updateTotalPrice();
                updateSeatSelection();
            });
            movieSelect.addEventListener('change', updateTotalPrice);

            increaseTicketButton.addEventListener('click', function() {
                ticketAmountInput.value = parseInt(ticketAmountInput.value) + 1;
                updateTotalPrice();
                updateSeatSelection();
            });

            decreaseTicketButton.addEventListener('click', function() {
                if (ticketAmountInput.value > 1) {
                    ticketAmountInput.value = parseInt(ticketAmountInput.value) - 1;
                    updateTotalPrice();
                    updateSeatSelection();
                }
            });

            form.addEventListener('submit', function(event) {
                if (buyTicketButton.disabled) {
                    event.preventDefault();
                    alert('Please select the seats before buying a ticket.');
                }
            });

            function updateSelectedSeatsInput() {
                selectedSeatsContainer.innerHTML = '';
                selectedSeats.forEach((seat, index) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `seat_number_${index}`;
                    input.value = seat;
                    selectedSeatsContainer.appendChild(input);
                });
            }

            // Initialize the total price on page load
            updateTotalPrice();
        });

        // Function to redirect the user to the main page
        function redirectToMainPage() {
            window.location.href = '../../../userMainPage.php';
        }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <a class="navbar-brand" href="#">CineSphere</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="../auth/mainPage.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="../auth/logout.php">Logout</a>
                </li>

            </ul>
            <a class="back-button" href="../../../userMainPage.php">Back</a>
        </div>
    </nav>

    <div class="container">
        <div class="main-content">
            <div class="user-info">
                <div class="section-title">User Info</div>
                <form method="POST" action="">
                    <?php
                    if (isset($_SESSION['email_status'])) {
                        $status_class = ($_SESSION['email_status_type'] === 'success') ? 'alert-info' : 'alert-error';
                        echo "<div class='$status_class'>
                                {$_SESSION['email_status']}<br>
                                <button class='btn' onclick='redirectToMainPage()'>OK</button>
                              </div>";
                        unset($_SESSION['email_status']);
                        unset($_SESSION['email_status_type']);
                    }
                    ?>
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['username']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" disabled>
                    </div>
                    <div class="form-group">
                        <label>Mobile Phone</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($user['phone_number']); ?>" disabled>
                    </div>
            </div>

            <div class="booking-info">
                <div class="section-title">Booking Info</div>
                <div class="form-group">
                    <label>Movie</label>
                    <select class="form-control" name="movie" required>
                        <option value="">Choose a Movie</option>
                        <!-- Populate with available movies -->
                        <?php
                        foreach ($all_movies as $movie) {
                            echo "<option value='" . $movie['id'] . "' data-price='" . $movie['price'] . "'>" . htmlspecialchars($movie['title']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cinema</label>
                    <select class="form-control" name="cinema" required>
                        <option value="">Choose a Cinema</option>
                        <!-- Populate with available cinemas -->
                        <?php
                        foreach ($all_cinemas as $cinema) {
                            echo "<option value='" . $cinema['id'] . "'>" . htmlspecialchars($cinema['name']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" class="form-control" name="date" required>
                </div>
                <div class="form-group">
                    <label>Time</label>
                    <select class="form-control" name="time" required>
                        <option value="">Choose a Time</option>
                        <?php
                        $times = [];
                        for ($hour = 11; $hour <= 24; $hour++) {
                            $times[] = sprintf('%02d:00', $hour % 24);
                            $times[] = sprintf('%02d:30', $hour % 24);
                        }
                        foreach ($times as $time) {
                            echo "<option value='" . $time . "'>" . htmlspecialchars($time) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <label>Ticket Amount</label>
                <div class="input-group mb-3">
                    <div class="input-group-prepend">
                        <button class="btn btn-outline-secondary" type="button" id="decreaseTicket">-</button>
                    </div>
                    <input type="number" class="form-control" id="ticket_amount" name="ticket_amount" min="1" value="1">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" id="increaseTicket">+</button>
                    </div>
                </div>
                <div class="total-price" id="total_price">Total Price: 0.00 EUR</div>
            </div>

            <div class="seat-selection">
                <div class="section-title">Available Seats</div>
                <div class="available-seats">
                    <?php
                    for ($i = 1; $i <= 50; $i++) {
                        $seat_number = strval($i);
                        echo "<div data-seat-number='$seat_number'>$seat_number</div>";
                    }
                    ?>
                </div>
            </div>

            <div id="selected-seats-container"></div>

            <div class="action-buttons">
                <button type="reset" class="btn reset-btn">Reset</button>
                <button type="button" class="btn logout-btn" onclick="window.location.href='../auth/logout.php'">Logout</button>
                <button type="submit" class="btn btn-success btn-buy-ticket" name="buy_ticket">Book Ticket</button>
            </div>
                </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
</body>
</html>
