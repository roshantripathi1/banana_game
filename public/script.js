/* ==========================
   GLOBAL STATE
   ========================== */
// Feature branch: easy-math version update

// Cards
let firstCard = null;
let secondCard = null;

// Board state
let lockBoard = false;
let matchedPairs = 0;

// Game values
let score = 0;
let totalPairs = 0;
let timerInterval = null;
let timeLeft = 0;
let initialTime = 0;

// Hints
let hintsLeft = 0;

// Math
let correctMathAnswer = null; // only one answer now

/* ==========================
   DOM ELEMENTS
   ========================== */

const board            = document.getElementById("game-board");
const scoreDisplay     = document.getElementById("score");
const timerDisplay     = document.getElementById("timer");
const hintsDisplay     = document.getElementById("hints");
const levelDisplay     = document.getElementById("level");
const startButton      = document.getElementById("start-game");
const difficultySelect = document.getElementById("difficulty");
const hintButton       = document.getElementById("use-hint");

let loadingBox = null;

/* ==========================
   LOADING OVERLAY
   ========================== */

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

function hideLoading() {
    if (loadingBox) {
        loadingBox.remove();
        loadingBox = null;
    }
}

/* ==========================
   DIFFICULTY SETTINGS
   ========================== */

function getDifficultySettings() {
    const diff = difficultySelect.value;
    if (diff === "beginner")     return { pairs: 4, time: 40, hints: 2 };
    if (diff === "intermediate") return { pairs: 6, time: 50, hints: 1 };
    return { pairs: 8, time: 60, hints: 0 }; // advanced
}

/* ==========================
   EVENT BINDINGS
   ========================== */

startButton.addEventListener("click", startNewGame);
difficultySelect.addEventListener("change", loadLeaderboard);
if (hintButton) hintButton.addEventListener("click", useHint);
document.getElementById("submit-math").addEventListener("click", onSubmitMath);

/* ==========================
   NEW GAME
   ========================== */

function startNewGame() {
    showLoading();

    const settings = getDifficultySettings();
    totalPairs   = settings.pairs;
    initialTime  = settings.time;
    timeLeft     = settings.time;
    matchedPairs = 0;
    score        = 0;
    firstCard    = null;
    secondCard   = null;
    lockBoard    = false;

    hintsLeft = settings.hints;

    scoreDisplay.textContent = score;
    timerDisplay.textContent = timeLeft;
    hintsDisplay.textContent = hintsLeft;
    levelDisplay.textContent = 1;
    board.innerHTML = "";

    startLevel();
    loadLeaderboard();
}

/* ==========================
   MATH CHALLENGE (EASIER)
   ========================== */

// Load Banana puzzle or simple fallback addition
async function loadMathPuzzle() {
    const questionEl  = document.getElementById("math-question");
    const answerInput = document.getElementById("math-answer");
    const submitBtn   = document.getElementById("submit-math");
    const imgEl       = document.getElementById("banana-image");
    const feedbackEl  = document.getElementById("math-feedback");

    // Reset math UI
    correctMathAnswer      = null;
    feedbackEl.textContent = "";
    questionEl.textContent = "Loading Banana puzzle...";
    answerInput.value      = "";
    answerInput.disabled   = true;
    submitBtn.disabled     = true;
    if (imgEl) imgEl.style.display = "none";

    try {
        const res  = await fetch("banana_api.php");
        const data = await res.json();

        // If Banana works and answer is numeric → use it
        if (!data.error && data.answer !== null && typeof data.answer !== "undefined") {
            correctMathAnswer = data.answer;

            questionEl.textContent = "Solve: What is the correct number?";
            if (imgEl && data.image_url) {
                imgEl.src = data.image_url;
                imgEl.style.display = "block";
            }

            answerInput.disabled = false;
            submitBtn.disabled   = false;
            return;
        }

        // If Banana failed → easier fallback
        generateEasyAdditionQuestion();

        answerInput.disabled = false;
        submitBtn.disabled   = false;

    } catch (e) {
        console.error("Banana API error:", e);

        // Fallback to easy addition
        generateEasyAdditionQuestion();
        answerInput.disabled = false;
        submitBtn.disabled   = false;
    }
}

// Simple addition 1–10 + 1–10
function generateEasyAdditionQuestion() {
    const questionEl = document.getElementById("math-question");
    const feedbackEl = document.getElementById("math-feedback");
    const imgEl      = document.getElementById("banana-image");

    if (imgEl) imgEl.style.display = "none";

    const a = Math.floor(Math.random() * 10) + 1;
    const b = Math.floor(Math.random() * 10) + 1;

    correctMathAnswer = a + b;
    questionEl.textContent = `Solve: What is ${a} + ${b}?`;
    feedbackEl.textContent = "(Easy question: small numbers only.)";
}

// Handle submit for single easy math question
function onSubmitMath() {
    const answerInput = document.getElementById("math-answer");
    const feedbackEl  = document.getElementById("math-feedback");
    const userAnswer  = parseInt(answerInput.value, 10);

    if (Number.isNaN(userAnswer)) {
        feedbackEl.textContent = "Please enter a number.";
        return;
    }

    if (correctMathAnswer === null) {
        feedbackEl.textContent = "No puzzle loaded.";
        return;
    }

    if (userAnswer === correctMathAnswer) {
        feedbackEl.textContent = "Correct! +10 points!";
        score += 10;
        scoreDisplay.textContent = score;
    } else {
        feedbackEl.textContent =
            `Incorrect. The correct answer was ${correctMathAnswer}.`;
    }

    // Save score after answering
    saveScoreToServer();

    // Disable further changes
    answerInput.disabled = true;
    document.getElementById("submit-math").disabled = true;
}

/* ==========================
   LEVEL INIT
   ========================== */

function startLevel() {
    const symbols = generateSymbols(totalPairs);
    shuffle(symbols);
    createBoard(symbols);
    loadMathPuzzle();
    startTimer();
    setTimeout(hideLoading, 500);
}

/* ==========================
   SYMBOLS + SHUFFLE
   ========================== */

function generateSymbols(pairCount) {
    const emojiSet = ["🍎", "🍌", "🍇", "🍒", "🍍", "🥑", "🥝", "🍉"];
    const selected = emojiSet.slice(0, pairCount);
    return [...selected, ...selected];
}

function shuffle(array) {
    for (let i = array.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [array[i], array[j]] = [array[j], array[i]];
    }
}

/* ==========================
   BOARD + CARDS
   ========================== */

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

function handleCardClick(card) {
    if (lockBoard) return;
    if (!card.classList.contains("hidden")) return;
    if (firstCard === card) return;

    card.classList.remove("hidden");

    if (!firstCard) {
        firstCard = card;
        return;
    }

    secondCard = card;
    checkForMatch();
}

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
            if (cardA && !cardA.classList.contains("matched"))
                cardA.classList.add("hidden");
            if (cardB && !cardB.classList.contains("matched"))
                cardB.classList.add("hidden");

            firstCard = null;
            secondCard = null;
            lockBoard = false;
        }, 800);
    }
}

/* ==========================
   HINTS
   ========================== */

function useHint() {
    if (hintsLeft <= 0) {
        alert("No hints left.");
        return;
    }

    const cards = Array.from(document.querySelectorAll(".card"));
    const map = {};

    cards.forEach(card => {
        if (card.classList.contains("matched")) return;
        const sym = card.dataset.symbol;
        if (!map[sym]) map[sym] = [];
        map[sym].push(card);
    });

    let pair = null;
    for (const sym in map) {
        if (map[sym].length >= 2) {
            pair = map[sym].slice(0, 2);
            break;
        }
    }

    if (!pair) {
        alert("No pairs available.");
        return;
    }

    hintsLeft--;
    hintsDisplay.textContent = hintsLeft;

    pair.forEach(c => c.classList.remove("hidden"));
    setTimeout(() => {
        pair.forEach(c => {
            if (!c.classList.contains("matched")) c.classList.add("hidden");
        });
    }, 1000);
}

/* ==========================
   PUZZLE COMPLETED
   ========================== */

function onPuzzleCompleted() {
    clearInterval(timerInterval);
    document.getElementById("math-feedback").textContent =
        "Puzzle done! Now answer the math question.";
}

/* ==========================
   TIMER
   ========================== */

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

/* ==========================
   SAVE SCORE
   ========================== */

function saveScoreToServer() {
    const guestFlag = (typeof IS_GUEST === "undefined") ? true : IS_GUEST;
    if (guestFlag) {
        console.log("Guest: score not saved.");
        return;
    }

    const diff = difficultySelect.value;
    const timeUsed = initialTime - timeLeft;
    const safeTime = timeUsed >= 0 ? timeUsed : 0;

    fetch("save_score.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
            score: score,
            time:  safeTime,
            difficulty: diff
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            console.log("Score saved.");
            loadLeaderboard();
        } else {
            console.error("Save score error:", data);
        }
    })
    .catch(err => console.error("Save score failed:", err));
}

/* ==========================
   LEADERBOARD
   ========================== */

async function loadLeaderboard() {
    const diff = difficultySelect.value;

    try {
        const res  = await fetch("get_leaderboard.php?difficulty=" + encodeURIComponent(diff));
        const data = await res.json();

        const tbody = document.getElementById("leaderboard-body");
        if (!tbody) return;

        tbody.innerHTML = "";

        data.forEach((row, index) => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td>${row.username}</td>
                <td>${row.score}</td>
                <td>${row.time_taken}</td>
            `;
            tbody.appendChild(tr);
        });

    } catch (err) {
        console.error("Leaderboard error:", err);
    }
}

/* ==========================
   INITIAL LOAD
   ========================== */

document.addEventListener("DOMContentLoaded", () => {
    loadLeaderboard();
});
