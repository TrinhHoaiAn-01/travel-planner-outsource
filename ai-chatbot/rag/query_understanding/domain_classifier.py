# pipeline hoạt động: load data → validate → train → evaluate → save model → predict domain

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
DATA_PATH = PROJECT_DIR / "data" / "domain_training.json"
MODEL_DIR = Path(__file__).resolve().parent / "models"
MODEL_PATH = MODEL_DIR / "domain_classifier.joblib"


def load_training_data():
    # đọc dữ liệu huấn luyện
    with open(DATA_PATH, "r", encoding="utf-8") as file:
        data = json.load(file)

    # kiểm tra cấu trúc dữ liệu
    if not isinstance(data, list) or not data:
        raise ValueError("Dữ liệu huấn luyện phải là danh sách JSON không rỗng.")

    queries = []
    domains = []

    for index, item in enumerate(data):
        if not isinstance(item, dict):
            raise ValueError(
                f"Dữ liệu tại index {index} phải là một object."
            )

        query = item.get("query")
        domain = item.get("domain")

        if not isinstance(query, str) or not query.strip():
            raise ValueError(
                f"Query tại index {index} không hợp lệ."
            )

        if not isinstance(domain, str) or not domain.strip():
            raise ValueError(
                f"Domain tại index {index} không hợp lệ."
            )

        queries.append(query.strip())
        domains.append(domain.strip())

    # kiểm tra số lượng domain
    unique_domains = set(domains)

    if len(unique_domains) < 2:
        raise ValueError(
            "Dữ liệu huấn luyện phải có ít nhất 2 domain khác nhau."
        )

    # kiểm tra số mẫu tối thiểu của mỗi domain
    domain_counts = {}

    for domain in domains:
        if domain not in domain_counts:
            domain_counts[domain] = 0

        domain_counts[domain] += 1

    for domain, count in domain_counts.items():
        if count < 2:
            raise ValueError(
                f"Domain '{domain}' cần ít nhất 2 mẫu."
            )

    return queries, domains


def create_domain_model():
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


def train_domain_classifier():
    # load dữ liệu
    queries, domains = load_training_data()

    # chia dữ liệu train và test
    X_train, X_test, y_train, y_test = train_test_split(
        queries,
        domains,
        test_size=0.2,
        random_state=42,
        stratify=domains
    )

    # khởi tạo và huấn luyện model
    model = create_domain_model()
    model.fit(X_train, y_train)

    # dự đoán trên tập test
    predictions = model.predict(X_test)

    # đánh giá kết quả
    accuracy = accuracy_score(y_test, predictions)

    print("\n--- DOMAIN CLASSIFIER EVALUATION ---")
    print("Accuracy:", round(accuracy, 4))
    print(
        classification_report(
            y_test,
            predictions,
            zero_division=0
        )
    )

    return model


def save_domain_classifier(model):
    # tạo thư mục lưu model nếu chưa tồn tại
    MODEL_DIR.mkdir(parents=True, exist_ok=True)

    # lưu toàn bộ pipeline
    joblib.dump(model, MODEL_PATH)

    print("Model đã lưu tại:", MODEL_PATH)


@lru_cache(maxsize=1)
def load_domain_classifier():
    # kiểm tra model đã được huấn luyện và lưu hay chưa
    if not MODEL_PATH.exists():
        raise FileNotFoundError(
            f"Không tìm thấy model tại: {MODEL_PATH}. "
            "Hãy chạy file này để huấn luyện model trước."
        )

    # tải pipeline đã lưu
    model = joblib.load(MODEL_PATH)

    return model


def predict_domain(query, model=None):
    # kiểm tra câu hỏi đầu vào
    if not isinstance(query, str) or not query.strip():
        raise ValueError(
            "Query phải là chuỗi và không được để trống."
        )

    # tải model nếu chưa được truyền vào
    if model is None:
        model = load_domain_classifier()

    # dự đoán domain
    domain = model.predict([query.strip()])[0]

    return domain
