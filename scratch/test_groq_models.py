import urllib.request
import json
import sys

sys.stdout.reconfigure(encoding='utf-8')

key = "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"
url = "https://api.groq.com/openai/v1/chat/completions"

models = ["llama-3.3-70b-versatile", "llama-3.1-8b-instant", "mixtral-8x7b-32768", "gemma2-9b-it", "openai/gpt-oss-120b"]

for m in models:
    payload = {
        "model": m,
        "messages": [{"role": "user", "content": "Hello SEO assistant!"}]
    }
    req = urllib.request.Request(
        url,
        data=json.dumps(payload).encode('utf-8'),
        headers={
            'Content-Type': 'application/json',
            'Authorization': f'Bearer {key}',
            'User-Agent': 'Mozilla/5.0'
        }
    )
    try:
        with urllib.request.urlopen(req) as resp:
            data = json.loads(resp.read().decode('utf-8'))
            reply = data['choices'][0]['message']['content']
            print(f"MODEL '{m}': SUCCESS (200 OK) -> {reply[:80]}...")
    except Exception as e:
        print(f"MODEL '{m}': FAILED -> {e}")
