<?php
session_start();
include 'conn.php';  // Database connection

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php"); // Redirect to login page if not logged in
    exit;
}

$user_id = $_SESSION['user_id'];

// Check if the user has already taken the quiz
$query = $conn->query("SELECT * FROM quiz_results WHERE user_id = $user_id");
if ($query->num_rows > 0) {
    echo "<h1 style='text-align:center;color:#e74c3c;'>You have already taken the quiz!</h1>";
    echo "<button style='display:block;margin:auto;padding:10px;background-color:#3498db;color:white;border:none;border-radius:5px;' onclick=\"window.location.href='index.php'\">Back to Index</button>";
    exit;
}

// Fetch 10 random quiz questions from the database
$questions = $conn->query("SELECT * FROM quiz_questions ORDER BY RAND() LIMIT 10");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ensure that the answers are provided
    if (isset($_POST['answers']) && is_array($_POST['answers'])) {
        $score = 0;

        // Calculate time taken
        $start_time = $_POST['start_time'];  // Start time stored in hidden input
        $end_time = time();  // Current time when the quiz is submitted
        $time_taken = $end_time - $start_time;  // Time in seconds

        // Format the time taken into H:i:s (hours:minutes:seconds)
        $formatted_time = gmdate("H:i:s", $time_taken);

        // Calculate score
        foreach ($_POST['answers'] as $question_id => $answer) {
            $query = $conn->query("SELECT correct_option FROM quiz_questions WHERE id = $question_id");
            $correct_answer = $query->fetch_assoc()['correct_option'];
            if ($answer == $correct_answer) {
                $score++;
            }
        }

        // Insert the result into quiz_results table
        $stmt = $conn->prepare("INSERT INTO quiz_results (user_id, score, time_taken) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $user_id, $score, $formatted_time);
        $stmt->execute();

        // Show the result
        echo "<h1 style='text-align:center;color:#27ae60;'>Your Score: $score</h1>";
        echo "<h2 style='text-align:center;color:#3498db;'>Time Taken: $formatted_time</h2>";

        // Provide option to go back to the index to try again if not already taken
        echo "<button style='display:block;margin:auto;padding:10px;background-color:#3498db;color:white;border:none;border-radius:5px;' onclick=\"window.location.href='index.php'\">Back to Index</button>";

        exit;
    } else {
        // Display an error if no answers are provided
        echo "<h1 style='text-align:center;color:#e74c3c;'>Please answer all questions before submitting!</h1>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quiz</title>
    <link rel="stylesheet" href="style.css"> <!-- External CSS -->
    <script>
        // JavaScript to check if all questions are answered before submitting the form
        function validateQuizForm() {
            var allAnswered = true;
            var questions = document.querySelectorAll('.question');
            questions.forEach(function (question) {
                var selectedAnswer = question.querySelector('input[type="radio"]:checked');
                if (!selectedAnswer) {
                    allAnswered = false;
                    question.style.border = '2px solid red';  // Highlight unanswered question
                } else {
                    question.style.border = '';  // Remove highlight for answered questions
                }
            });

            if (!allAnswered) {
                alert("Please answer all questions before submitting.");
            }

            return allAnswered;  // Return false if not all questions are answered
        }
    </script>
</head>
<body>
    <h1>Welcome to the Quiz, <?php echo $_SESSION['first_name']; ?>!</h1>

    <!-- Quiz Form -->
    <form method="POST" onsubmit="return validateQuizForm()">
        <!-- Capture the start time when the form is loaded -->
        <input type="hidden" name="start_time" value="<?php echo time(); ?>">

        <?php while ($row = $questions->fetch_assoc()): ?>
            <?php
            // Shuffle the options randomly for each question
            $options = array($row['option_a'], $row['option_b'], $row['option_c'], $row['option_d']);
            shuffle($options);
            ?>

            <div class="question">
                <p><?php echo $row['question_text']; ?></p>
                <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="A" required> <?php echo $options[0]; ?><br>
                <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="B" required> <?php echo $options[1]; ?><br>
                <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="C" required> <?php echo $options[2]; ?><br>
                <input type="radio" name="answers[<?php echo $row['id']; ?>]" value="D" required> <?php echo $options[3]; ?><br>
            </div>
        <?php endwhile; ?>

        <button type="submit">Submit Quiz</button>
    </form>
</body>
</html>
