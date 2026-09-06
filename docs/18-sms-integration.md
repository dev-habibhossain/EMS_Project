# 18 — SMS Integration

## Use cases

| Event | Default |
|---|---|
| Sale confirmation | On if phone present and opt-in |
| Payment confirmation | On |
| Due reminder | Manual button |
| Dispatch | Optional |

## Architecture

```
Event → Listener → SendSmsJob → SmsManager → ProviderDriver
                              ↘ sms_logs
```

`SmsManager` binds driver from settings (`sms.driver=log|ssl|bulksmsbd|twilio`).

Do **not** hard-code a vendor. Bangladesh gateways change; log driver for dev.

## Credentials

Encrypted in `settings` or `.env`. Never in frontend.

## Queue & retry

Backoff 3 attempts. Failed → status `failed`, visible in log UI. No silent loss.

## Opt-in

`customers.sms_opt_in` default true for MVP with ability to turn off. Respect opt-out.

## Content

Templates in lang files, 160–400 chars, include invoice no and amount. Avoid Unicode multipart surprises; test Bangla GSM vs Unicode.

## Webhooks

Optional delivery receipt endpoint per provider.
