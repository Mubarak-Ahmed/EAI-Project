<?php
$client = new SoapClient(null, [
    'location' => 'http://localhost/Project/server.php',
    'uri'      => 'http://localhost/Project/server.php',
    'trace'    => 1
]);

// ✅ Always load reserved seats (even on GET)
try {
    $reservedList = $client->getReservedSeats();
$reservedSeatIds = array_filter(explode(",", trim($reservedList)), 'strlen');
} catch (SoapFault $e) {
    $reservedSeatIds = [];
}

// ✅ Booking logic only on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selectedSeats = $_POST['selectedSeats'] ?? '';
    try {
        $result = $client->bookSeats($selectedSeats);
        // Redirect to same page to force refresh of reserved seats
        header("Location: client.php?success=" . urlencode($result));
        exit();
    } catch (SoapFault $e) {
        header("Location: client.php?error=" . urlencode($e->getMessage()));
        exit();
    }
}
?>


<?php if (isset($_GET['success'])): ?>
    <script>alert("<?= htmlspecialchars($_GET['success']) ?>");</script>
<?php elseif (isset($_GET['error'])): ?>
    <script>alert("Booking failed: <?= htmlspecialchars($_GET['error']) ?>");</script>
<?php endif; ?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stadium Seat Booking</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
    const reservedSeatsFromServer = <?php echo json_encode($reservedSeatIds); ?>;
</script>

    <style>
        body {
            background: #222;
            color: #fff;
            font-family: Arial, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stadium-container {
            position: relative;
            width: 500px;
            height: 500px;
            margin: 40px auto;
        }
        .stadium-field {
            position: absolute;
            top: 50%; left: 50%;
            width: 160px; height: 160px;
            background: #4caf50;
            border: 4px solid #fff;
            box-shadow: 0 0 20px #222;
            transform: translate(-50%, -50%);
            z-index: 2;
        }
        .seats-row, .seats-col {
            position: absolute;
            display: flex;
            z-index: 3;
        }
        .seats-row.top {
            left: 50%;
            transform: translateX(-50%);
            flex-direction: row;
        }
        .seats-row.top.r1 { top: 60px; }
        .seats-row.top.r2 { top: 30px; }
        .seats-row.top.r3 { top: 0px; }
        .seats-row.top.r4 { top: -30px; }
        .seats-row.top.r5 { top: -60px; }
        .seats-row.top.r6 { top: -90px; }
        .seats-row.top.r7 { top: -120px; }
        .seats-row.bottom {
            left: 50%;
            transform: translateX(-50%);
            flex-direction: row;
        }
        .seats-row.bottom.r1 { bottom: 60px; }
        .seats-row.bottom.r2 { bottom: 30px; }
        .seats-row.bottom.r3 { bottom: 0px; }
        .seats-row.bottom.r4 { bottom: -30px; }
        .seats-row.bottom.r5 { bottom: -60px; }
        .seats-row.bottom.r6 { bottom: -90px; }
        .seats-row.bottom.r7 { bottom: -120px; }
        .seats-col.left {
            top: 50%;
            transform: translateY(-50%);
            flex-direction: column;
        }
        .seats-col.left.c1 { left: 60px; }
        .seats-col.left.c2 { left: 30px; }
        .seats-col.left.c3 { left: 0px; }
        .seats-col.left.c4 { left: -30px; }
        .seats-col.left.c5 { left: -60px; }
        .seats-col.left.c6 { left: -90px; }
        .seats-col.left.c7 { left: -120px; }
        .seats-col.right {
            top: 50%;
            transform: translateY(-50%);
            flex-direction: column;
        }
        .seats-col.right.c1 { right: 60px; }
        .seats-col.right.c2 { right: 30px; }
        .seats-col.right.c3 { right: 0px; }
        .seats-col.right.c4 { right: -30px; }
        .seats-col.right.c5 { right: -60px; }
        .seats-col.right.c6 { right: -90px; }
        .seats-col.right.c7 { right: -120px; }
        .stadium-seat {
            width: 32px; height: 32px;
            background: #eee;
            border-radius: 50%;
            border: 2px solid #bbb;
            box-shadow: 0 2px 6px #0002;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            margin: 6px;
        }
        .stadium-seat:hover {
            background: #ffd700;
            box-shadow: 0 0 12px #ffd70088;
            transform: scale(1.15);
            z-index: 4;
        }
        .stadium-seat.selected {
            background: #ffd700;
            border-color: #ffd700;
        }

        .stadium-seat.unavailable {
    background: #d32f2f; /* red */
    border-color: #b71c1c;
    cursor: not-allowed;
    pointer-events: none;
}

        .stadium-legend {
            margin-top: 40px;
            display: flex;
            gap: 24px;
            font-size: 16px;
            align-items: center;
            justify-content: center;
        }
        .legend-seat {
            display: inline-block;
            width: 20px; height: 20px;
            border-radius: 50%;
            margin-right: 6px;
            border: 2px solid #bbb;
            vertical-align: middle;
        }
        .legend-seat.available { background: #eee; }
        .legend-seat.selected  { background: #ffd700; border-color: #ffd700;}
       .legend-seat.unavailable {
    background: #d32f2f;
    border-color: #b71c1c;
}

        /* Make sure the form and legend are below the stadium */
        .stadium-form-wrapper {
       
        }
    </style>
</head>
<body>
    <div class="stadium-container">
        <div class="stadium-field"></div>
        <!-- Top rows -->
        <div class="seats-row top r1">
            <div class="stadium-seat" data-seat-id="seat-0"></div><div class="stadium-seat" data-seat-id="seat-1"></div><div class="stadium-seat" data-seat-id="seat-2"></div>
            <div class="stadium-seat" data-seat-id="seat-3"></div><div class="stadium-seat" data-seat-id="seat-4"></div><div class="stadium-seat" data-seat-id="seat-5"></div>
        </div>
        <div class="seats-row top r2">
            <div class="stadium-seat" data-seat-id="seat-6"></div><div class="stadium-seat" data-seat-id="seat-7"></div><div class="stadium-seat" data-seat-id="seat-8"></div>
            <div class="stadium-seat" data-seat-id="seat-9"></div><div class="stadium-seat" data-seat-id="seat-10"></div><div class="stadium-seat" data-seat-id="seat-11"></div>
        </div>
        <div class="seats-row top r3">
            <div class="stadium-seat" data-seat-id="seat-12"></div><div class="stadium-seat" data-seat-id="seat-13"></div><div class="stadium-seat" data-seat-id="seat-14"></div>
            <div class="stadium-seat" data-seat-id="seat-15"></div><div class="stadium-seat" data-seat-id="seat-16"></div><div class="stadium-seat" data-seat-id="seat-17"></div>
        </div>
        <div class="seats-row top r4">
            <div class="stadium-seat" data-seat-id="seat-18"></div><div class="stadium-seat" data-seat-id="seat-19"></div><div class="stadium-seat" data-seat-id="seat-20"></div>
            <div class="stadium-seat" data-seat-id="seat-21"></div><div class="stadium-seat" data-seat-id="seat-22"></div><div class="stadium-seat" data-seat-id="seat-23"></div>
        </div>
        <div class="seats-row top r5">
            <div class="stadium-seat" data-seat-id="seat-24"></div><div class="stadium-seat" data-seat-id="seat-25"></div><div class="stadium-seat" data-seat-id="seat-26"></div>
            <div class="stadium-seat" data-seat-id="seat-27"></div><div class="stadium-seat" data-seat-id="seat-28"></div><div class="stadium-seat" data-seat-id="seat-29"></div>
        </div>
        <div class="seats-row top r6">
            <div class="stadium-seat" data-seat-id="seat-30"></div><div class="stadium-seat" data-seat-id="seat-31"></div><div class="stadium-seat" data-seat-id="seat-32"></div>
            <div class="stadium-seat" data-seat-id="seat-33"></div><div class="stadium-seat" data-seat-id="seat-34"></div><div class="stadium-seat" data-seat-id="seat-35"></div>
        </div>
        <div class="seats-row top r7">
            <div class="stadium-seat" data-seat-id="seat-36"></div><div class="stadium-seat" data-seat-id="seat-37"></div><div class="stadium-seat" data-seat-id="seat-38"></div>
            <div class="stadium-seat" data-seat-id="seat-39"></div><div class="stadium-seat" data-seat-id="seat-40"></div><div class="stadium-seat" data-seat-id="seat-41"></div>
        </div>
        <!-- Bottom rows -->
        <div class="seats-row bottom r1">
            <div class="stadium-seat" data-seat-id="seat-42"></div><div class="stadium-seat" data-seat-id="seat-43"></div><div class="stadium-seat" data-seat-id="seat-44"></div>
            <div class="stadium-seat" data-seat-id="seat-45"></div><div class="stadium-seat" data-seat-id="seat-46"></div><div class="stadium-seat" data-seat-id="seat-47"></div>
        </div>
        <div class="seats-row bottom r2">
            <div class="stadium-seat" data-seat-id="seat-48"></div><div class="stadium-seat" data-seat-id="seat-49"></div><div class="stadium-seat" data-seat-id="seat-50"></div>
            <div class="stadium-seat" data-seat-id="seat-51"></div><div class="stadium-seat" data-seat-id="seat-52"></div><div class="stadium-seat" data-seat-id="seat-53"></div>
        </div>
        <div class="seats-row bottom r3">
            <div class="stadium-seat" data-seat-id="seat-54"></div><div class="stadium-seat" data-seat-id="seat-55"></div><div class="stadium-seat" data-seat-id="seat-56"></div>
            <div class="stadium-seat" data-seat-id="seat-57"></div><div class="stadium-seat" data-seat-id="seat-58"></div><div class="stadium-seat" data-seat-id="seat-59"></div>
        </div>
        <div class="seats-row bottom r4">
            <div class="stadium-seat" data-seat-id="seat-60"></div><div class="stadium-seat" data-seat-id="seat-61"></div><div class="stadium-seat" data-seat-id="seat-62"></div>
            <div class="stadium-seat" data-seat-id="seat-63"></div><div class="stadium-seat" data-seat-id="seat-64"></div><div class="stadium-seat" data-seat-id="seat-65"></div>
        </div>
        <div class="seats-row bottom r5">
            <div class="stadium-seat" data-seat-id="seat-66"></div><div class="stadium-seat" data-seat-id="seat-67"></div><div class="stadium-seat" data-seat-id="seat-68"></div>
            <div class="stadium-seat" data-seat-id="seat-69"></div><div class="stadium-seat" data-seat-id="seat-70"></div><div class="stadium-seat" data-seat-id="seat-71"></div>
        </div>
        <div class="seats-row bottom r6">
            <div class="stadium-seat" data-seat-id="seat-72"></div><div class="stadium-seat" data-seat-id="seat-73"></div><div class="stadium-seat" data-seat-id="seat-74"></div>
            <div class="stadium-seat" data-seat-id="seat-75"></div><div class="stadium-seat" data-seat-id="seat-76"></div><div class="stadium-seat" data-seat-id="seat-77"></div>
        </div>
        <div class="seats-row bottom r7">
            <div class="stadium-seat" data-seat-id="seat-78"></div><div class="stadium-seat" data-seat-id="seat-79"></div><div class="stadium-seat" data-seat-id="seat-80"></div>
            <div class="stadium-seat" data-seat-id="seat-81"></div><div class="stadium-seat" data-seat-id="seat-82"></div><div class="stadium-seat" data-seat-id="seat-83"></div>
        </div>
        <!-- Left columns -->
        <div class="seats-col left c1">
            <div class="stadium-seat" data-seat-id="seat-84"></div><div class="stadium-seat" data-seat-id="seat-85"></div><div class="stadium-seat" data-seat-id="seat-86"></div>
            <div class="stadium-seat" data-seat-id="seat-87"></div><div class="stadium-seat" data-seat-id="seat-88"></div><div class="stadium-seat" data-seat-id="seat-89"></div>
        </div>
        <div class="seats-col left c2">
            <div class="stadium-seat" data-seat-id="seat-90"></div><div class="stadium-seat" data-seat-id="seat-91"></div><div class="stadium-seat" data-seat-id="seat-92"></div>
            <div class="stadium-seat" data-seat-id="seat-93"></div><div class="stadium-seat" data-seat-id="seat-94"></div><div class="stadium-seat" data-seat-id="seat-95"></div>
        </div>
        <div class="seats-col left c3">
            <div class="stadium-seat" data-seat-id="seat-96"></div><div class="stadium-seat" data-seat-id="seat-97"></div><div class="stadium-seat" data-seat-id="seat-98"></div>
            <div class="stadium-seat" data-seat-id="seat-99"></div><div class="stadium-seat" data-seat-id="seat-100"></div><div class="stadium-seat" data-seat-id="seat-101"></div>
        </div>
        <div class="seats-col left c4">
            <div class="stadium-seat" data-seat-id="seat-102"></div><div class="stadium-seat" data-seat-id="seat-103"></div><div class="stadium-seat" data-seat-id="seat-104"></div>
            <div class="stadium-seat" data-seat-id="seat-105"></div><div class="stadium-seat" data-seat-id="seat-106"></div><div class="stadium-seat" data-seat-id="seat-107"></div>
        </div>
        <div class="seats-col left c5">
            <div class="stadium-seat" data-seat-id="seat-108"></div><div class="stadium-seat" data-seat-id="seat-109"></div><div class="stadium-seat" data-seat-id="seat-110"></div>
            <div class="stadium-seat" data-seat-id="seat-111"></div><div class="stadium-seat" data-seat-id="seat-112"></div><div class="stadium-seat" data-seat-id="seat-113"></div>
        </div>
        <div class="seats-col left c6">
            <div class="stadium-seat" data-seat-id="seat-114"></div><div class="stadium-seat" data-seat-id="seat-115"></div><div class="stadium-seat" data-seat-id="seat-116"></div>
            <div class="stadium-seat" data-seat-id="seat-117"></div><div class="stadium-seat" data-seat-id="seat-118"></div><div class="stadium-seat" data-seat-id="seat-119"></div>
        </div>
        <div class="seats-col left c7">
            <div class="stadium-seat" data-seat-id="seat-120"></div><div class="stadium-seat" data-seat-id="seat-121"></div><div class="stadium-seat" data-seat-id="seat-122"></div>
            <div class="stadium-seat" data-seat-id="seat-123"></div><div class="stadium-seat" data-seat-id="seat-124"></div><div class="stadium-seat" data-seat-id="seat-125"></div>
        </div>
        <!-- Right columns -->
        <div class="seats-col right c1">
            <div class="stadium-seat" data-seat-id="seat-126"></div><div class="stadium-seat" data-seat-id="seat-127"></div><div class="stadium-seat" data-seat-id="seat-128"></div>
            <div class="stadium-seat" data-seat-id="seat-129"></div><div class="stadium-seat" data-seat-id="seat-130"></div><div class="stadium-seat" data-seat-id="seat-131"></div>
        </div>
        <div class="seats-col right c2">
            <div class="stadium-seat" data-seat-id="seat-132"></div><div class="stadium-seat" data-seat-id="seat-133"></div><div class="stadium-seat" data-seat-id="seat-134"></div>
            <div class="stadium-seat" data-seat-id="seat-135"></div><div class="stadium-seat" data-seat-id="seat-136"></div><div class="stadium-seat" data-seat-id="seat-137"></div>
        </div>
        <div class="seats-col right c3">
            <div class="stadium-seat" data-seat-id="seat-138"></div><div class="stadium-seat" data-seat-id="seat-139"></div><div class="stadium-seat" data-seat-id="seat-140"></div>
            <div class="stadium-seat" data-seat-id="seat-141"></div><div class="stadium-seat" data-seat-id="seat-142"></div><div class="stadium-seat" data-seat-id="seat-143"></div>
        </div>
        <div class="seats-col right c4">
            <div class="stadium-seat" data-seat-id="seat-144"></div><div class="stadium-seat" data-seat-id="seat-145"></div><div class="stadium-seat" data-seat-id="seat-146"></div>
            <div class="stadium-seat" data-seat-id="seat-147"></div><div class="stadium-seat" data-seat-id="seat-148"></div><div class="stadium-seat" data-seat-id="seat-149"></div>
        </div>
        <div class="seats-col right c5">
            <div class="stadium-seat" data-seat-id="seat-150"></div><div class="stadium-seat" data-seat-id="seat-151"></div><div class="stadium-seat" data-seat-id="seat-152"></div>
            <div class="stadium-seat" data-seat-id="seat-153"></div><div class="stadium-seat" data-seat-id="seat-154"></div><div class="stadium-seat" data-seat-id="seat-155"></div>
        </div>
        <div class="seats-col right c6">
            <div class="stadium-seat" data-seat-id="seat-156"></div><div class="stadium-seat" data-seat-id="seat-157"></div><div class="stadium-seat" data-seat-id="seat-158"></div>
            <div class="stadium-seat" data-seat-id="seat-159"></div><div class="stadium-seat" data-seat-id="seat-160"></div><div class="stadium-seat" data-seat-id="seat-161"></div>
        </div>
        <div class="seats-col right c7">
            <div class="stadium-seat" data-seat-id="seat-162"></div><div class="stadium-seat" data-seat-id="seat-163"></div><div class="stadium-seat" data-seat-id="seat-164"></div>
            <div class="stadium-seat" data-seat-id="seat-165"></div><div class="stadium-seat" data-seat-id="seat-166"></div><div class="stadium-seat" data-seat-id="seat-167"></div>
        </div>
    </div>
    <div class="stadium-form-wrapper">
        <form action="client.php" method="POST" id="bookingForm">
    <input type="hidden" name="selectedSeats" id="selectedSeatsInput">

    <div style="margin-bottom: 10px;">
        <input type="text" name="name" id="nameInput" placeholder="Enter your name" required
               style="padding: 8px; border-radius: 5px; border: none; width: 200px;">
    </div>
    <div style="margin-bottom: 10px;">
        <input type="email" name="email" id="emailInput" placeholder="Enter your email" required
               style="padding: 8px; border-radius: 5px; border: none; width: 200px;">
    </div>

    <button type="submit" id="submitBtn" style="
        padding: 10px 20px;
        font-size: 16px;
        background: #4caf50;
        color: white;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        box-shadow: 0 2px 6px #000;
    ">Submit Booking</button>
</form>

        <div class="stadium-legend">
            <div><div class="legend-seat available"></div>Available</div>
            <div><div class="legend-seat selected"></div>Selected</div>
            <div><div class="legend-seat unavailable"></div>Reserved</div>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const seats = document.querySelectorAll('.stadium-seat');
    const selectedSeats = new Set();

    function updateHiddenField() {
        document.getElementById('selectedSeatsInput').value = Array.from(selectedSeats).join(',');
    }

    seats.forEach((seat) => {
        const id = seat.dataset.seatId;

        if (reservedSeatsFromServer && reservedSeatsFromServer.includes(id)) {
            seat.classList.add('unavailable');
            return;
        }

        seat.addEventListener('click', () => {
            if (seat.classList.contains('selected')) {
                seat.classList.remove('selected');
                selectedSeats.delete(id);
            } else {
                seat.classList.add('selected');
                selectedSeats.add(id);
            }
            updateHiddenField();
        });
    });

    document.getElementById('bookingForm').addEventListener('submit', function (e) {
        updateHiddenField(); // 🔁 Ensure field is updated first

        const name = document.getElementById('nameInput').value.trim();
        const email = document.getElementById('emailInput').value.trim();
        const selected = document.getElementById('selectedSeatsInput').value.trim();

        if (!selected) {
            alert("Please select at least one seat.");
            e.preventDefault();
            return;
        }

        if (!name || !email) {
            alert("Please fill in your name and email before booking.");
            e.preventDefault();
            return;
        }
    });
});
</script>

</body>
</html>
