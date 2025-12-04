/* ============================================================
   GLOBAL VARIABLES
   ============================================================ */

// Track selected cards
let firstCard = null;
let secondCard = null;

// Game state
let lockBoard = false;
let matchedPairs = 0;
let totalPairs = 0;

// Game stats
let score = 0;
let timeLeft = 0;
let timerInterval = null;

// Banana API math answer
let correctMathAnswer = null;

/* Element references */
const board = document.getElementById("game-board");
const scoreDisplay = document.getElementById("score");
const timerDisplay = document.getElementById("timer");
const hintsDisplay = document.getElementById("hints");
const levelDisplay = document.getElementById("level");

const startButton = document.getElementById("start-game");
const difficultySelect = document.getElementById("difficulty");

// Loading overlay variable
let loadingBox = null;


/* ============================================================
   LOADING SPINNER UI
   ============================================================ */

// Show loading spinner when game starts
function showLoading() {
    if (loadingBox) return;

    loadingBox = document.createElement("div");
    loadingBox.className = "loading-overlay";
    loadingBox.innerHTML = `
        <div class="loading-box">
            <div class="spinner"></div>
            <p>Loading puzzle...</p>
        </div>
    `;
    document.body.appendChild(loadingBox);
}

// Remove loading spinner
function hideLoading() {
    if (loadingBox) {
        loadingBox.remove();
        loadingBox = null;
    }
}


/* ============================================================
   DIFFICULTY SETTINGS
   ============================================================ */

function getDifficultySettings() {
    const diff = difficultySelect.value;

    if (diff === "beginner")
        return { pairs: 4, time: 40, hints: 2 };

    if (diff === "intermediate")
        return { pairs: 6, time: 50, hints: 1 };

    return { pairs: 8, time: 60, hints: 0 }; // advanced
}


/* ============================================================
   START NEW GAME
   ============================================================ */

startButton.addEventListener("click", startNewGame);
difficultySelect.addEventListener("change", loadLeaderboard);

function startNewGame() {
    showLoading(); // show spinner

    const settings = getDifficultySettings();

    totalPairs = settings.pairs;
    timeLeft = settings.time;
    matchedPairs = 0;
    score = 0;

    firstCard = null;
    secondCard = null;

    // Update UI
    scoreDisplay.textContent = score;
    hintsDisplay.textContent = settings.hints;
    levelDisplay.textContent = 1;
    board.innerHTML = "";

    startLevel();
    loadLeaderboard();
}


/* ============================================================
   LOAD BANANA API PUZZLE
   ============================================================ */

async function loadMathPuzzle() {
    try {
        // Call our PHP endpoint, which calls Banana API
        const response = await fetch("banana_api.php");
        const data = await response.json();

        const questionEl = document.getElementById("math-question");
        const answerInput = document.getElementById("math-answer");
        const submitBtn = document.getElementById("submit-math");
        const imgEl = document.getElementById("banana-image");

        // Handle failure
        if (data.error) {
            questionEl.textContent = "Error loading puzzle.";
            correctMathAnswer = null;
            if (imgEl) imgEl.style.display = "none";
            answerInput.disabled = true;
            submitBtn.disabled = true;
            return;
        }

        // Store correct answer
        correctMathAnswer = data.answer;

        // Update description
        questionEl.textContent = "Look at the image and solve the missing number.";

        // Show image from API
        if (imgEl && data.image_url) {
            imgEl.src = data.image_url;
            imgEl.style.display = "block";
        }

        // Enable inputs
        answerInput.disabled = false;
        submitBtn.disabled = false;

    } catch (err) {
        console.error("Banana API error:", err);
    }
}


// Answer submission
document.getElementById("submit-math").addEventListener("click", () => {
    const userAnswer = parseInt(document.getElementById("math-answer").value, 10);

    if (isNaN(userAnswer)) {
        document.getElementById("math-feedback").textContent = "Enter a number.";
        return;
    }

    if (userAnswer === correctMathAnswer) {
        document.getElementById("math-feedback").textContent = "Correct! +20 points!";
        score += 20;
    } else {
        document.getElementById("math-feedback").textContent = "Incorrect!";
    }

    scoreDisplay.textContent = score;
});


/* ============================================================
   START LEVEL: puzzle board + math puzzle
   ============================================================ */

function startLevel() {
    const symbols = generateSymbols(totalPairs);
    shuffle(symbols);
    createBoard(symbols);

    loadMathPuzzle();   // load Banana puzzle
    startTimer();       // start game timer

    setTimeout(hideLoading, 600); // remove loading
}


/* ============================================================
   PUZZLE SYMBOLS
   ============================================================ */

function generateSymbols(pairCount) {
    const emoji = ["🍎", "🍌", "🍇", "🍒", "🍍", "🥑", "🥝", "🍉"];
    const selected = emoji.slice(0, pairCount);
    return [...selected, ...selected]; // duplicate for pairs
}


/* ============================================================
   SHUFFLE CARDS
   ============================================================ */

function shuffle(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}


/* ============================================================
   CREATE CARD BOARD
   ============================================================ */

function createBoard(symbols) {
    board.innerHTML = "";

    symbols.forEach(symbol => {
        const card = document.createElement("div");
        card.className = "card hidden";
        card.dataset.symbol = symbol;

        const span = document.createElement("span");
        span.textContent = symbol;

        card.appendChild(span);
        card.addEventListener("click", () => handleCardClick(card));

        board.appendChild(card);
    });
}


/* ============================================================
   CARD CLICK LOGIC
   ============================================================ */

function handleCardClick(card) {
    if (lockBoard) return;
    if (!card.classList.contains("hidden")) return;
    if (card === firstCard) return;

    // Flip card
    card.classList.remove("hidden");

    if (!firstCard) {
        firstCard = card;
        return;
    }

    secondCard = card;
    checkForMatch();
}


/* ============================================================
   CHECK MATCH (with null safety)
   ============================================================ */

function checkForMatch() {
    if (!firstCard || !secondCard) return;

    const cardA = firstCard;
    const cardB = secondCard;

    const isMatch = cardA.dataset.symbol === cardB.dataset.symbol;

    if (isMatch) {
        cardA.classList.add("matched");
        cardB.classList.add("matched");

        score += 10;
        scoreDisplay.textContent = score;

        matchedPairs++;

        if (matchedPairs === totalPairs) {
            onPuzzleCompleted();
        }

        firstCard = null;
        secondCard = null;
    } else {
        lockBoard = true;

        setTimeout(() => {
            if (!cardA.classList.contains("matched")) cardA.classList.add("hidden");
            if (!cardB.classList.contains("matched")) cardB.classList.add("hidden");

            firstCard = null;
            secondCard = null;
            lockBoard = false;
        }, 700);
    }
}


/* ============================================================
   PUZZLE COMPLETED
   ============================================================ */

function onPuzzleCompleted() {
    clearInterval(timerInterval);
    document.getElementById("math-feedback").textContent =
        "Puzzle completed! Now solve the math challenge.";
}


/* ============================================================
   TIMER
   ============================================================ */

function startTimer() {
    clearInterval(timerInterval);

    timerInterval = setInterval(() => {
        timeLeft--;
        timerDisplay.textContent = timeLeft;

        if (timeLeft <= 0) {
            clearInterval(timerInterval);
            lockBoard = true;
            alert("Time up!");
        }
    }, 1000);
}


/* ============================================================
   LEADERBOARD DISPLAY (No date)
   ============================================================ */

async function loadLeaderboard() {
    try {
        const diff = difficultySelect.value;
        const response = await fetch("get_leaderboard.php?difficulty=" + diff);
        const data = await response.json();

        const list = document.getElementById("leaderboard");
        list.innerHTML = "";

        data.forEach(row => {
            const li = document.createElement("li");
            li.textContent = `${row.username}: ${row.score} pts — ${row.time_taken}s`;
            list.appendChild(li);
        });

    } catch (err) {
        console.error("Leaderboard error:", err);
    }
}


/* ============================================================
   INITIAL LOAD
   ============================================================ */

document.addEventListener("DOMContentLoaded", loadLeaderboard);
