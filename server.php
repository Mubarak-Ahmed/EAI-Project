<?php
class BookingService
{
    private $file = "seats.txt";

    public function bookSeats($seatsString)
    {
        $seats = array_filter(explode(",", $seatsString), 'strlen');

        // Load existing booked seats from file
        $reservedSeats = [];
        if (file_exists($this->file)) {
            $reservedSeats = array_filter(explode(",", trim(file_get_contents($this->file))), 'strlen');
        }
        
        $alreadyReserved = array_intersect($reservedSeats, $seats);
        if (count($alreadyReserved) > 0) {
            return "These seats are already reserved: " . implode(", ", $alreadyReserved);
        }

        $updatedSeats = array_unique(array_merge($reservedSeats, $seats));
        file_put_contents($this->file, implode(",", $updatedSeats));

        return "Booking successful for: " . implode(", ", $seats);
    }

    public function getReservedSeats()
    {
        if (!file_exists($this->file)) return "";
        return trim(file_get_contents($this->file));
    }
}

$server = new SoapServer(null, [
    "uri" => "http://localhost/Project/server.php"
]);
$server->setClass("BookingService");
$server->handle();
?>

