# Muthaka Admin Portal and API Features

## 1. Purpose

This document lists the required features for:

- The **Laravel admin portal**.
- The **Laravel API** consumed by the Flutter mobile app and widgets.

Muthaka is a widget-first couples app. The admin portal should help the team manage users, couples, safety, content, payments, and app health. The APIs should support onboarding, partner pairing, widget updates, relationship interactions, privacy, and subscriptions.

## 2. Admin Portal Overview

The admin portal is for internal staff only.

Recommended implementation:

- Laravel Blade + Tailwind, or
- Filament Admin for faster development.

Admin roles:

- **Super Admin**: full access.
- **Support Admin**: user support, reports, account checks.
- **Content Admin**: prompts, categories, app content.
- **Finance Admin**: payments, subscriptions, refunds.
- **Read Only Admin**: view dashboards and reports only.

## 3. Admin Portal Features

### 3.1 Admin Authentication

Features:

- Admin login.
- Password reset.
- Role-based access control.
- Two-factor authentication if possible.
- Login activity log.
- Session timeout.

### 3.2 Dashboard

Metrics:

- Total users.
- Total active couples.
- New users today.
- New couples paired today.
- Daily active users.
- Daily active couples.
- Widget updates today.
- Mood sends today.
- Notes sent today.
- Snaps sent today.
- Doodles sent today.
- Prompt answers today.
- Premium subscriptions.
- Revenue this month.
- Failed payments.
- Reports pending review.

Useful charts:

- User growth.
- Pairing conversion.
- Widget interactions.
- Retention.
- Revenue.
- Feature usage.

### 3.3 User Management

Features:

- View all users.
- Search by name, phone, email, or user ID.
- Filter by status, date joined, subscription status, country, language.
- View user profile.
- View linked partner/couple.
- View device tokens.
- View login history.
- View latest activity.
- Suspend user.
- Unsuspend user.
- Delete/deactivate user.
- Force logout user.
- Reset user pairing if support-approved.

User profile fields:

- Name.
- Phone.
- Email.
- Avatar.
- Language.
- Country.
- Timezone.
- Account status.
- Subscription status.
- Last seen.
- Created date.

### 3.4 Couple Management

Features:

- View all couples.
- Search by either partner.
- View couple profile.
- View pairing status.
- View invite history.
- View active widgets.
- View latest widget state.
- View interaction summary.
- Disconnect couple manually.
- Mark couple as blocked/ended.
- Resolve pairing issues.

Couple metrics:

- Pair date.
- Last interaction.
- Mood count.
- Note count.
- Doodle count.
- Snap count.
- Prompt answer count.
- Current streak.

### 3.5 Invite Management

Features:

- View pending invites.
- Search invite code.
- View inviter and invitee phone.
- View invite expiry.
- Resend invite.
- Cancel invite.
- Expire old invites.
- Detect invite abuse or spam.

### 3.6 Widget Management

Features:

- View latest widget state per couple/user.
- View widget version.
- View active widgets.
- View widget refresh attempts.
- View failed widget syncs.
- Manually trigger widget sync for support.
- Disable a problematic widget type if needed.

Widget types:

- Mood.
- Note.
- Doodle.
- Snap.
- Distance.
- Countdown.
- Daily prompt.

### 3.7 Mood Management

Features:

- View mood events.
- Filter by mood type.
- View mood usage statistics.
- Configure allowed mood options.
- Enable/disable mood presets.

Default moods:

- Happy.
- Stressed.
- Excited.
- Tired.
- Missing you.
- Calm.

### 3.8 Notes Management

Features:

- View note event metadata.
- Search notes only when policy allows.
- Moderate reported notes.
- Configure note presets.
- Disable abusive preset usage.

Important:

Private couple notes should not be casually visible to admins. If note content is accessible, access must be logged and limited to support/moderation cases.

### 3.9 Doodle Management

Features:

- View doodle metadata.
- View reported doodles.
- Remove abusive doodle media.
- Monitor storage usage.
- View upload failures.

### 3.10 Snap / Media Management

Features:

- View snap metadata.
- View reported snaps.
- Remove unsafe media.
- Monitor storage size.
- Monitor media processing failures.
- Configure file size limits.
- Configure allowed image formats.

Important:

Media should be private. Admin access to media should be permission-controlled and logged.

### 3.11 Countdown Management

Features:

- View countdowns.
- View popular countdown types.
- Remove abusive countdown titles if reported.
- Configure suggested countdown templates.

Suggested templates:

- Date night.
- Anniversary.
- Next visit.
- Wedding.
- Birthday.
- Vacation.

### 3.12 Prompt and Quiz Management

Features:

- Create prompt.
- Edit prompt.
- Delete/archive prompt.
- Activate/deactivate prompt.
- Assign prompt category.
- Set language.
- Mark prompt as free or premium.
- Schedule daily prompts.
- View prompt performance.
- View answer completion rate.

Prompt fields:

- Question.
- Language: English, Swahili, Sheng optional.
- Category.
- Tone.
- Is premium.
- Is active.

Prompt categories:

- Appreciation.
- Fun.
- Future.
- Adventure.
- Life habits.
- Intimacy.
- Family.
- Conflict repair.
- Faith/values optional.

Quiz management:

- Create quiz.
- Add questions.
- Add answer options.
- Set scoring.
- View quiz completion rates.

### 3.13 Streak Management

Features:

- View couple streaks.
- Reset streak for support cases.
- Configure streak rules.
- View missed streaks.
- Configure streak reminders.

### 3.14 Subscription and Payment Management

Features:

- View plans.
- Create/edit subscription plans.
- View payments.
- Search by user, phone, transaction ID, provider reference.
- View payment status.
- View failed payments.
- Manually mark payment after verification.
- Cancel subscription.
- Extend subscription.
- Refund record tracking.
- Export payment report.

Payment providers:

- M-Pesa.
- Card provider later if needed.

Plan examples:

- Free.
- Premium monthly: KES 100.
- Premium monthly plus: KES 250.
- Annual plan.

### 3.15 Notification Management

Features:

- View notification templates.
- Create/edit templates.
- Send test notification.
- Send targeted notification to user.
- Send broadcast notification to all users.
- Send broadcast to premium users.
- Send broadcast to inactive users.
- View delivery status if available.

Notification types:

- Partner invite.
- Mood update.
- Note received.
- Doodle received.
- Snap received.
- Prompt reminder.
- Streak reminder.
- Subscription reminder.

### 3.16 AI Management

For future DeepSeek integration.

Features:

- Configure AI prompt templates.
- Enable/disable AI features.
- View AI usage count.
- View AI cost estimates.
- View failed AI requests.
- Moderate AI-generated suggestions.
- Configure safety rules.

AI features:

- Smart mood suggestions.
- Auto-generated love notes.
- Personalized daily prompts.
- Relationship insights.
- Sentiment summaries.

### 3.17 Reports and Safety

Features:

- View reports.
- Filter by type and status.
- Assign report to admin.
- Review reported user.
- Review reported note/media.
- Warn user.
- Suspend user.
- Block account.
- Mark report resolved.
- Add internal notes.

Report types:

- Harassment.
- Abusive message.
- Unsafe media.
- Fake account.
- Privacy concern.
- Payment issue.
- Other.

### 3.18 Privacy and Consent Management

Features:

- View user privacy settings.
- View distance-sharing consent.
- View AI consent.
- View notification consent.
- View data export requests.
- View deletion requests.
- Process account deletion.

Privacy controls:

- Share distance.
- Approximate distance only.
- Save mood history.
- AI suggestions.
- Snap expiry.
- Quiet hours.

### 3.19 Support Tools

Features:

- Search user by phone.
- View pairing status.
- Resend verification code.
- Resend partner invite.
- Clear stuck invite.
- Trigger widget sync.
- View app version.
- View device platform.
- View recent API errors for user.
- Add internal support note.

### 3.20 Content Pages Management

For landing page and legal pages.

Features:

- Manage FAQ.
- Manage landing page content.
- Manage pricing copy.
- Manage privacy policy.
- Manage terms of service.
- Manage help articles.

### 3.21 System Health

Features:

- Queue status.
- Failed jobs.
- API error logs.
- Push notification failures.
- Storage usage.
- MySQL size.
- Redis status.
- Scheduler last run.
- WebSocket/Reverb status.
- App version adoption.

### 3.22 Audit Logs

Track admin actions:

- Login.
- User view.
- User suspension.
- Couple disconnect.
- Payment update.
- Media view/removal.
- Prompt changes.
- Settings changes.

Audit log fields:

- Admin ID.
- Action.
- Entity type.
- Entity ID.
- IP address.
- User agent.
- Metadata.
- Created at.

## 4. Laravel API Feature List

Base path:

```text
/api/v1
```

Authentication:

```text
Authorization: Bearer {sanctum_token}
```

## 5. Public APIs

### 5.1 App Configuration

```text
GET /config
```

Returns:

- Supported app version.
- Feature flags.
- Available languages.
- Payment plans.
- Support contacts.

### 5.2 Landing Page / Waitlist

```text
POST /waitlist
POST /contact
```

Used by Laravel landing page.

## 6. Auth APIs

### 6.1 Register

```text
POST /auth/register
```

Fields:

- name
- phone
- email optional
- password
- language

### 6.2 Login

```text
POST /auth/login
```

Fields:

- phone or email
- password

### 6.3 Logout

```text
POST /auth/logout
```

### 6.4 Current User

```text
GET /me
PUT /me
DELETE /me
```

### 6.5 Password Reset

```text
POST /auth/forgot-password
POST /auth/reset-password
```

### 6.6 Phone Verification

```text
POST /auth/send-otp
POST /auth/verify-otp
```

## 7. Device APIs

### 7.1 Register Device Token

```text
POST /devices
```

Fields:

- platform: android, ios
- fcm_token
- device_name
- app_version

### 7.2 Delete Device Token

```text
DELETE /devices/{id}
```

### 7.3 Update App Version

```text
PUT /devices/{id}
```

## 8. Couple Pairing APIs

### 8.1 Create Invite

```text
POST /couple/invite
```

Fields:

- partner_phone

Returns:

- invite_code
- invite_link
- expiry

### 8.2 Accept Invite

```text
POST /couple/accept
```

Fields:

- invite_code

### 8.3 Current Couple

```text
GET /couple
```

### 8.4 Disconnect Couple

```text
DELETE /couple
```

### 8.5 Block Partner

```text
POST /couple/block
```

## 9. Widget State APIs

### 9.1 Get Latest Widget State

```text
GET /widget-state
```

Returns:

- widget version.
- latest mood.
- latest note.
- latest doodle.
- latest snap.
- latest distance.
- active countdown.
- daily prompt.

### 9.2 Sync Widget State

```text
POST /widget-state/sync
```

Fields:

- local_version
- platform
- active_widgets

### 9.3 Widget Settings

```text
GET /widget-settings
PUT /widget-settings
```

Fields:

- enabled widgets.
- widget theme.
- refresh preference.

## 10. Mood APIs

### 10.1 Send Mood

```text
POST /moods
```

Fields:

- mood_key
- message optional

### 10.2 Latest Mood

```text
GET /moods/latest
```

### 10.3 Mood History

```text
GET /moods/history
```

Query:

- from
- to
- limit

## 11. Note APIs

### 11.1 Send Note

```text
POST /notes
```

Fields:

- body
- preset_key optional

### 11.2 Latest Note

```text
GET /notes/latest
```

### 11.3 Note History

```text
GET /notes/history
```

### 11.4 Note Presets

```text
GET /notes/presets
```

## 12. Doodle APIs

### 12.1 Send Doodle

```text
POST /doodles
```

Fields:

- image file
- vector_json optional
- caption optional

### 12.2 Latest Doodle

```text
GET /doodles/latest
```

### 12.3 Doodle History

```text
GET /doodles/history
```

### 12.4 Delete Doodle

```text
DELETE /doodles/{id}
```

## 13. Snap APIs

### 13.1 Send Snap

```text
POST /snaps
```

Fields:

- image file
- caption optional
- expires_at optional

### 13.2 Latest Snap

```text
GET /snaps/latest
```

### 13.3 Snap History

```text
GET /snaps/history
```

### 13.4 Delete Snap

```text
DELETE /snaps/{id}
```

## 14. Distance APIs

### 14.1 Update Distance

```text
POST /distance
```

Fields:

- latitude optional
- longitude optional
- approximate_distance_km optional
- precision

Important:

The API should store/share approximate distance, not exact live location, unless exact sharing is explicitly added later with clear consent.

### 14.2 Latest Distance

```text
GET /distance/latest
```

### 14.3 Distance Settings

```text
GET /distance/settings
PUT /distance/settings
```

Fields:

- share_distance
- approximate_only

## 15. Countdown APIs

### 15.1 Create Countdown

```text
POST /countdowns
```

Fields:

- title
- event_date
- emoji optional

### 15.2 List Countdowns

```text
GET /countdowns
```

### 15.3 Update Countdown

```text
PUT /countdowns/{id}
```

### 15.4 Delete Countdown

```text
DELETE /countdowns/{id}
```

### 15.5 Set Active Countdown

```text
POST /countdowns/{id}/set-active
```

## 16. Prompt APIs

### 16.1 Today Prompt

```text
GET /prompts/today
```

### 16.2 Answer Prompt

```text
POST /prompts/{id}/answer
```

Fields:

- answer

### 16.3 Prompt History

```text
GET /prompts/history
```

### 16.4 Prompt Categories

```text
GET /prompts/categories
```

### 16.5 Couple Quizzes

```text
GET /quizzes
GET /quizzes/{id}
POST /quizzes/{id}/answer
```

## 17. Moments / Memories APIs

### 17.1 Moments Timeline

```text
GET /moments
```

Query:

- type
- from
- to
- limit

### 17.2 Save Memory

```text
POST /memories
```

Fields:

- type
- source_event_id
- title
- note optional

### 17.3 List Memories

```text
GET /memories
```

### 17.4 Delete Memory

```text
DELETE /memories/{id}
```

## 18. Streak APIs

### 18.1 Current Streak

```text
GET /streak
```

### 18.2 Streak History

```text
GET /streak/history
```

## 19. Subscription and Payment APIs

### 19.1 Plans

```text
GET /plans
```

### 19.2 Start Subscription

```text
POST /subscriptions/start
```

Fields:

- plan_id
- provider

### 19.3 Current Subscription

```text
GET /subscription
```

### 19.4 Cancel Subscription

```text
POST /subscription/cancel
```

### 19.5 M-Pesa STK Push

```text
POST /payments/mpesa/stk-push
```

Fields:

- phone
- amount
- plan_id

### 19.6 M-Pesa Callback

```text
POST /payments/mpesa/callback
```

This endpoint is called by the payment provider.

### 19.7 Payment History

```text
GET /payments
```

## 20. Notification APIs

### 20.1 Notification Settings

```text
GET /notifications/settings
PUT /notifications/settings
```

Fields:

- mood_updates
- notes
- snaps
- doodles
- prompts
- quiet_hours_start
- quiet_hours_end

### 20.2 Notification Inbox

```text
GET /notifications
```

### 20.3 Mark Notification Read

```text
POST /notifications/{id}/read
```

## 21. Privacy APIs

### 21.1 Privacy Settings

```text
GET /privacy
PUT /privacy
```

Fields:

- share_distance
- approximate_distance_only
- save_mood_history
- allow_ai_suggestions
- allow_snap_history
- quiet_hours_enabled

### 21.2 Data Export Request

```text
POST /privacy/data-export
```

### 21.3 Account Deletion Request

```text
POST /privacy/delete-account
```

## 22. Report and Safety APIs

### 22.1 Report User or Content

```text
POST /reports
```

Fields:

- report_type
- entity_type
- entity_id
- reason
- description optional

### 22.2 Block User

```text
POST /blocks
```

### 22.3 Unblock User

```text
DELETE /blocks/{id}
```

## 23. AI APIs

Future feature.

### 23.1 Smart Suggestions

```text
GET /ai/suggestions
```

### 23.2 Generate Love Note

```text
POST /ai/love-note
```

Fields:

- tone
- context optional

### 23.3 Generate Prompt

```text
POST /ai/prompts/generate
```

Admin-only or internal.

## 24. Admin API Features

Admin APIs should be separate from mobile APIs.

Base path:

```text
/admin
```

or

```text
/api/admin
```

Admin API modules:

- Admin auth.
- Dashboard metrics.
- User management.
- Couple management.
- Invite management.
- Content management.
- Prompt management.
- Payment management.
- Report moderation.
- Notification broadcasts.
- System health.
- Audit logs.

## 25. API Security Requirements

Every API must include:

- Authentication where required.
- Authorization checks.
- Request validation.
- Rate limiting.
- Consistent JSON responses.
- Audit logs for sensitive operations.
- File upload validation.
- Secure media URLs.

Critical authorization rule:

```text
A user can only access couple data if they are one of the two active partners.
```

## 26. Recommended MVP Admin Features

Build these first:

1. Admin login.
2. Dashboard metrics.
3. User list and user profile.
4. Couple list and couple profile.
5. Invite management.
6. Widget state viewer.
7. Prompt management.
8. Payment viewer.
9. Report viewer.
10. Audit logs.

## 27. Recommended MVP APIs

Build these first:

1. Auth APIs.
2. Device token APIs.
3. Couple invite and accept APIs.
4. Widget state API.
5. Mood APIs.
6. Note APIs.
7. Countdown APIs.
8. Notification settings API.
9. Privacy settings API.
10. Basic report API.

## 28. Later APIs

Add after MVP:

- Doodle APIs.
- Snap APIs.
- Prompt answers.
- Memories.
- Streaks.
- Payments.
- AI suggestions.
- Advanced admin broadcasts.

## 29. Final Recommendation

Keep the admin portal practical and operational. The most important admin value is not editing every feature. It is being able to answer:

- Who is the user?
- Are they paired?
- Did their widget update?
- Did the push notification send?
- Did payment work?
- Is there a safety/privacy issue?
- Is the system healthy?

