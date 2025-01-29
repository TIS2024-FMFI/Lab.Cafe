import requests
import time
import json

printers_url = 'http://localhost/otello/admin/api/get_printers'
webhook_url = 'http://localhost/otello/admin/printers/webhook'

def get_printers(url):
    try:
        response = requests.get(url)
        response.raise_for_status()
        return response.json().get('printers', [])
    except requests.exceptions.RequestException as e:
        print(f"Failed to get printers {url}")
        return []

def fetch_with_retry(url, headers, max_retries=2, retry_delay=2):
    attempt = 0
    while attempt < max_retries:
        try:
            response = requests.get(url, headers=headers, timeout=10)
            response.raise_for_status()
            return response.json()
        except requests.exceptions.RequestException as e:
            attempt += 1
            if attempt < max_retries:
                time.sleep(retry_delay)
            else:
                print(f"Failed to fetch data from {url}")
                return None

def send_data_to_webhook(url, data, printer_name):
    try:
        webhook_response = requests.post(url, json=data)
        webhook_response.raise_for_status()
        print(f"Successfully sent data for printer '{printer_name}'. Response: {webhook_response.text}")
    except requests.exceptions.RequestException as e:
        print(f"Failed to send data for printer '{printer_name}' {e}")

tlaciarne = get_printers(printers_url)

while True:

    if int(time.time()) % 300 == 0:
        tlaciarne = get_printers(printers_url)

    for tlaciaren in tlaciarne:
        name = tlaciaren.get('name', 'unknown')
        address = tlaciaren.get('address', '')
        api_key = tlaciaren.get('api_key', '')

        if not address or not api_key:
            print(f"Skipping printer '{name}' due to missing address or API key.")
            data = {
                    'topic': "Missing address or API key",
                    'deviceIdentifier': name,
                    'extra': '{"name": "", "progress": ""}',
                }
            
            send_data_to_webhook(webhook_url, data, name)
            continue

        print(f"Processing printer: {name} at {address}")

        octopi_job_url = f'http://{address}/api/job'
        octopi_printer_url = f'http://{address}/api/printer'
        headers = {'X-Api-Key': api_key}

        printer_data = fetch_with_retry(octopi_printer_url, headers)
        if not printer_data:
            print(f"Failed to fetch printer data for printer '{name}'.")
            data = {
                    'topic': "Failed to fetch printer data",
                    'deviceIdentifier': name,
                    'extra': '{"name": "", "progress": ""}',
                }
            
            send_data_to_webhook(webhook_url, data, name)
            continue
        
        job_data = fetch_with_retry(octopi_job_url, headers)
        if not job_data:
            print(f"Failed to fetch job data for printer '{name}'.")
            data = {
                    'topic': "Failed to fetch job data",
                    'deviceIdentifier': name,
                    'extra': '{"name": "", "progress": ""}',
                }
            
            send_data_to_webhook(webhook_url, data, name)
            continue
            
        topic = printer_data['state']['text']
        identifier = job_data['job']['user']
        extraDesc = job_data['job']['file']['name']
        jobDesc = job_data['job']['file']['name']

        if extraDesc is None or jobDesc is None:
            print(f"Failed to fetch job data for printer '{name}'.")
            data = {
                    'topic': "Failed to fetch job data",
                    'deviceIdentifier': name,
                    'extra': '{"name": "", "progress": ""}',
                }
            
            send_data_to_webhook(webhook_url, data, name)
            continue 

        progress = json.dumps({'progress': job_data['progress']})

        data = {
            'topic': topic,
            'deviceIdentifier': identifier,
            'extra': '{"name": "' + extraDesc + '", ' + progress[1:-1] + '}'
        }

        send_data_to_webhook(webhook_url, data, name)

    time.sleep(5)
