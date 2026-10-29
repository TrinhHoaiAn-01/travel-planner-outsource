# Website chatbot

The home-page assistant uses the existing embedding model, FAISS index, query router and AI provider. Start the services from separate terminals:

1. Start MySQL and Ollama (or configure another provider in `ai-chatbot/.env`). For the default provider, install the model with `ollama pull qwen2.5:0.5b`.
2. From `ai-chatbot`, install `requirements/requirements.txt` in a Python virtual environment and run `.venv\Scripts\python.exe web_api.py` on Windows (or `.venv/bin/python web_api.py` on Unix). The configured embedding model must already be cached, or set `EMBEDDING_LOCAL_FILES_ONLY=false` for its first download. The bridge listens only on `127.0.0.1:8765`; `CHATBOT_WEB_PORT` changes the port.
3. From `travel-planner-outsource`, run `php artisan serve` and open the home page. Laravel uses `CHATBOT_URL=http://127.0.0.1:8765` by default; if you change the bridge port, set the matching URL in Laravel's `.env` and clear cached configuration.

## Buttons and conversation history

The widget loads `GET /chat` when opened. New visitors see exactly three buttons: **Đặt phòng**, **Tham quan**, and **Khám phá điểm đến**. Clicking sends an allowlisted `action` to `POST /chat`. Laravel returns up to three actual destination records, their public detail links and listed prices. These actions query the live catalogue and do not require Python, embeddings or an LLM. Hotel results have room types; sightseeing results exclude destinations with room types. Inactive cities/categories are excluded. Prices are catalogue prices, not date-specific availability or booking confirmation.

For demo pacing, successful button replies have a random 1–6 second display delay in the widget while the existing loading indicator stays visible and duplicate submissions are blocked. This is simulated UI timing; it does not invoke an AI model. Free-text replies and errors have no added delay.

After each successful reply, three buttons remain available. Their city comes from the visitor's most recent city/destination mention, including Vietnamese without accents; a newer recognized city overrides an earlier one. The latest topic changes button ordering. This is an explicit, testable heuristic for suggesting the next question, not a learned prediction of what the user will say. It does not build itineraries or create bookings.

Laravel reads and writes the existing `ai_chatbot.chat_sessions` and `chat_messages` schema from `requirements/ai_chatbot.sql`. Import that schema if it is absent. Configure Laravel's `CHATBOT_DB_DATABASE` (default `ai_chatbot`); optional `CHATBOT_DB_HOST`, `CHATBOT_DB_PORT`, `CHATBOT_DB_USERNAME`, and `CHATBOT_DB_PASSWORD` default to Laravel's main database settings. Match these to Python's `MYSQL_*` settings when using the same chatbot database. No schema migration is required for this feature.

Website sessions use `channel_id=website` and `user_id=web-user:<id>` for signed-in visitors, or `web-guest:<random UUID>` for guests. Identity is established server-side. Clients cannot choose another conversation ID. Signed-in visitors can resume across browser sessions; guest access depends on their Laravel session cookie. Discord conversations are not imported automatically: their Discord identities require an explicit account mapping. The existing Laravel `ai_conversations` tables are a separate schema and are not used by this widget.

The latest 20 messages are restored from the database and forwarded to the free-text bridge as user/assistant text only. Recommendation cards are stored in message metadata for reloads. If the chatbot database is unavailable, the widget shows a session-only notice and keeps up to 20 pending messages for retry on the next successful send. Login/account changes clear the previous identity's cached and pending messages. Failed AI requests do not save incomplete turns.

Free text uses the existing retrieval/router/provider pipeline. RAG searches with multilingual E5 and BM25, ranks records and supplies them as evidence to the language model. This does not retrain Qwen or guarantee that it follows evidence. A public RAG query with no documents now returns a fixed no-data reply without invoking the model. Private transaction lookups/modifications are directed to account pages.

Check `AI_PROVIDER` in `ai-chatbot/.env`: the code default is Qwen 0.5B, but a local setting or process environment can select another provider. Do not infer the running provider from the code default.

The local bridge is single-process and has no authentication: keep it bound to loopback and use an authenticated, production-ready service before deploying across hosts. In Docker, `127.0.0.1` refers to each container; configure the bridge URL for your network separately.

## Verification

Run `php artisan test --filter ChatControllerTest` from the Laravel root. These tests use isolated in-memory SQLite databases for both schemas and mock the AI HTTP connection. They cover buttons, live records, prices/links, city context, inactive/empty results, ownership, recent history, provider failures and database recovery.

From `ai-chatbot`, run `.venv\Scripts\python.exe -m pytest tests -q`. To reproduce the retrieval audit, run `.venv\Scripts\python.exe rag/evaluate_retrieval.py --variant current --follow-up-rewrite on --label website_audit --output data/retrieval_website_audit_results.json`. For a small local Qwen generation comparison, run `.venv\Scripts\python.exe rag/evaluate_answers.py`; it explicitly uses Qwen independently of the live provider, with temperature zero and a 384-token output cap. Its phrase checks are smoke checks, not a quality score. Read `../docs/chatbot-audit.md` for findings and limits.
