<?php 
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['level'])) {
        $level = $_POST['level'];
        $_SESSION['level'] = $level;

        $_SESSION['chances'] = 5;

        // Difficulty level ranges
        switch ($level) {
            case 'easy':
                $min = 1;
                $max = 10;
                break;
            case 'medium':
                $min = 10;
                $max = 20;
                break;
            case 'hard':
                $min = 20;
                $max = 50;
                break;
        }

        // Generate random numbers for multiplication
        $num1 = rand($min, $max);
        $num2 = rand($min, $max);
        $_SESSION['num1'] = $num1;
        $_SESSION['num2'] = $num2;
        $_SESSION['answer'] = $num1 * $num2; // Update to multiplication
    }

    if (isset($_POST['user_answer'])) {
        $user_answer = $_POST['user_answer'];
        $_SESSION['chances']--;

        // Feedback based on the user's answer
        if ($user_answer < $_SESSION['answer']) {
            $message = "Your answer is too low! Try again!";
        } elseif ($user_answer > $_SESSION['answer']) {
            $message = "Your answer is too high! Try again!";
        } else {
            $message = "Correct! Well done!";
            $_SESSION['chances'] = 0; // End game on correct answer
        }

        // Game over message when chances are used up
        if ($_SESSION['chances'] == 0 && $user_answer != $_SESSION['answer']) {
            $message = "Game Over! The correct answer was " . $_SESSION['answer'] . ". Try again!";
        }
    }

    // Reset the game
    if (isset($_POST['back'])) {
        session_unset();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
} else {
    $message = "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Multiplication Guessing Game</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: gray;
        }
        .container {
            width: 90%;
            max-width: 600px;
            background:#c3e6cb ;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.25);
        }
        h1, h2 {
            color: blue;
            margin-bottom: 20px;
        }
        form {
            margin: 20px 0;
        }
        input[type="number"], input[type="radio"] {
            margin: 10px 0;
        }
        input[type="submit"] {
            padding: 10px 20px;
            background: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background: #45a049;
        }
        .message {
            padding: 10px;
            font-size: 16px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .message.success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .message.error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .chances {
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>MULTIPLICATION GUESSING GAME</h1>

    <?php if (empty($_SESSION['level'])): ?>
        <form method="post">
            <label for="level">Choose Difficulty Level:</label><br>
            <input type="radio" name="level" value="easy" id="easy"> Easy (1-10)<br>
            <input type="radio" name="level" value="medium" id="medium"> Medium (10-20)<br>
            <input type="radio" name="level" value="hard" id="hard"> Hard (20-50)<br><br>
            <input type="submit" value="Start Game">
        </form>
    <?php else: ?>
        <h2>Level: <?php echo ucfirst($_SESSION['level']); ?></h2>

        <?php if (isset($_SESSION['num1']) && isset($_SESSION['num2'])): ?>
            <p>Question: <?php echo $_SESSION['num1'] . " × " . $_SESSION['num2'] . " = ?"; ?></p> <!-- Updated to multiplication -->
        <?php endif; ?>

        <?php if ($_SESSION['chances'] > 0): ?>
            <form method="post">
                <label for="user_answer">Your Answer:</label>
                <input type="number" name="user_answer" required><br><br>
                <input type="submit" value="Submit Answer">
            </form>
        <?php else: ?>
            <p>You can restart the game by going back to the difficulty selection!</p>
        <?php endif; ?>

        <div class="chances">Chances left: <?php echo $_SESSION['chances']; ?></div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo strpos($message, 'Correct') !== false ? 'success' : 'error'; ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <input type="submit" name="back" value="Back to Difficulty Selection">
        </form>
    <?php endif; ?>
</div>
</body>
</html>
