# pipeline hoạt động: load data → validate → train → evaluate → save model → predict intent

import json
import joblib
from functools import lru_cache

from pathlib import Path
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.pipeline import Pipeline
from sklearn.metrics import accuracy_score, classification_report


# lấy thư mục gốc của project
PROJECT_DIR = Path(__file__).resolve().parents[2]

# xác định đường dẫn dữ liệu và model
DATA_PATH = PROJECT_DIR / "data" / "intent_training.json"
MODEL_DIR = Path(__file__).resolve().parent / "models"
MODEL_PATH = MODEL_DIR / "intent_classifier.joblib"


def load_training_data():
    # đọc dữ liệu huấn luyện
    if not DATA_PATH.exists():
        raise FileNotFoundError(
            f"Không tìm thấy dữ liệu huấn luyện tại: {DATA_PATH}"
        )

    with open(DATA_PATH, "r", encoding="utf-8") as file:
        data = json.load(file)

    # kiểm tra cấu trúc dữ liệu
    if not isinstance(data, list) or not data:
        raise ValueError("Dữ liệu huấn luyện phải là danh sách JSON không rỗng.")

    queries = []
    intents = []

    for index, item in enumerate(data):
        if not isinstance(item, dict):
            raise ValueError(
                f"Dữ liệu tại index {index} phải là một object."
            )

        query = item.get("query")
        intent = item.get("intent")

        if not isinstance(query, str) or not query.strip():
            raise ValueError(
                f"Query tại index {index} không hợp lệ."
            )

        if not isinstance(intent, str) or not intent.strip():
            raise ValueError(
                f"Intent tại index {index} không hợp lệ."
            )

        queries.append(query.strip())
        intents.append(intent.strip())

    # kiểm tra số lượng intent
    unique_intents = set(intents)

    if len(unique_intents) < 2:
        raise ValueError(
            "Dữ liệu huấn luyện phải có ít nhất 2 intent khác nhau."
        )

    # kiểm tra số mẫu tối thiểu của mỗi intent
    intent_counts = {}

    for intent in intents:
        if intent not in intent_counts:
            intent_counts[intent] = 0

        intent_counts[intent] += 1

    for intent, count in intent_counts.items():
        if count < 5:
            raise ValueError(
                f"Intent '{intent}' cần ít nhất 5 mẫu."
            )

    return queries, intents


def create_intent_model():
    # tạo pipeline tf-idf và logistic regression
    model = Pipeline(
        [
            (
                "tfidf",
                TfidfVectorizer(
                    lowercase=True,
                    ngram_range=(1, 2),
                    sublinear_tf=True
                )
            ),
            (
                "classifier",
                LogisticRegression(
                    max_iter=1000,
                    random_state=42
                )
            )
        ]
    )

    return model


def train_intent_classifier():
    # load dữ liệu
    queries, intents = load_training_data()

    # chia dữ liệu train và test
    X_train, X_test, y_train, y_test = train_test_split(
        queries,
        intents,
        test_size=0.2,
        random_state=42,
        stratify=intents
    )

    # khởi tạo và huấn luyện model
    model = create_intent_model()
    model.fit(X_train, y_train)

    # dự đoán trên tập test
    predictions = model.predict(X_test)

    # đánh giá kết quả
    accuracy = accuracy_score(y_test, predictions)

    print("\n--- INTENT CLASSIFIER EVALUATION ---")
    print("Accuracy:", round(accuracy, 4))
    print(
        classification_report(
            y_test,
            predictions,
            zero_division=0
        )
    )

    return model


def save_intent_classifier(model):
    # tạo thư mục lưu model nếu chưa tồn tại
    MODEL_DIR.mkdir(parents=True, exist_ok=True)

    # lưu toàn bộ pipeline
    joblib.dump(model, MODEL_PATH)

    print("Model đã lưu tại:", MODEL_PATH)


@lru_cache(maxsize=1)
def load_intent_classifier():
    # kiểm tra model đã được huấn luyện và lưu hay chưa
    if not MODEL_PATH.exists():
        raise FileNotFoundError(
            f"Không tìm thấy model tại: {MODEL_PATH}. "
            "Hãy chạy file này để huấn luyện model trước."
        )

    # tải pipeline đã lưu
    model = joblib.load(MODEL_PATH)

    return model


def predict_intent(query, model=None):
    # kiểm tra câu hỏi đầu vào
    if not isinstance(query, str) or not query.strip():
        raise ValueError(
            "Query phải là chuỗi và không được để trống."
        )

    # tải model nếu chưa được truyền vào
    if model is None:
        model = load_intent_classifier()

    # dự đoán intent
    intent = model.predict([query.strip()])[0]

    return intent

