import urllib.request
import json

base_url = 'http://localhost:8000/api'

def post(url, data, token=None):
    req = urllib.request.Request(url, data=json.dumps(data).encode('utf-8'))
    req.add_header('Content-Type', 'application/json')
    req.add_header('Accept', 'application/json')
    if token:
        req.add_header('Authorization', f'Bearer {token}')
    try:
        with urllib.request.urlopen(req) as response:
            return response.getcode(), json.loads(response.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode('utf-8'))

def get(url, token=None):
    req = urllib.request.Request(url)
    req.add_header('Accept', 'application/json')
    if token:
        req.add_header('Authorization', f'Bearer {token}')
    try:
        with urllib.request.urlopen(req) as response:
            return response.getcode(), json.loads(response.read().decode('utf-8'))
    except urllib.error.HTTPError as e:
        return e.code, json.loads(e.read().decode('utf-8'))

print("1. Logging in...")
code, body = post(f"{base_url}/login", {'email': 'tes@yaya.com', 'password': 'tes1234567'})
if code != 200:
    print(f"Login failed: {code} {body}")
    exit(1)

token = body.get('token') or body.get('access_token')
if not token and 'data' in body:
    token = body['data'].get('token') or body['data'].get('access_token')

print("Login OK")

journal_id = 9
journal_slug = 'test-journal'
print(f"2. Using Journal ID: {journal_id}")

print("3. Creating Submission...")
sub_data = {
    'journal_id': journal_id,
    'title': 'Runtime Verification Submission — Author Workspace',
    'abstract': 'This is a runtime verification submission created through the existing HexaLMS Author Workspace workflow.',
    'keywords': ['runtime verification', 'author workspace', 'hexalms'],
    'authors': [
        {
            'first_name': 'Tes',
            'last_name': 'User',
            'email': 'tes@yaya.com',
            'affiliation': 'Hexa',
            'is_corresponding': True
        }
    ]
}
code, body = post(f"{base_url}/submissions", sub_data, token)
if code != 201:
    print(f"Failed to create submission: {code} {body}")
    exit(1)

sub_id = body.get('data', {}).get('id') or body.get('id')
print(f"Created Submission ID: {sub_id}")

print("4. Verifying /author/submissions via API...")
code, body = get(f"{base_url}/submissions", token)
if code != 200:
    print(f"Failed to fetch submissions: {code} {body}")
    exit(1)

found = any(s['id'] == sub_id for s in body.get('data', []))
print(f"Submission in list: {'YES' if found else 'NO'}")

print("5. Verifying Submission Detail...")
code, body = get(f"{base_url}/submissions/{sub_id}", token)
print(f"Detail Code: {code}")
print(f"Detail Title: {body['data']['title']}")
print(f"Detail Status: {body['data']['status']}")

print("6. Check Editorial Visibility (Draft shouldn't be fully visible if restricted)")
code, body = get(f"{base_url}/user/journals/{journal_slug}/management/submissions", token)
print(f"Editorial access (should be 403, 404, or empty array since it's an author): {code}")

