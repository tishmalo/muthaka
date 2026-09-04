# Tuko / Connect App Layout Specification

## 1. Layout Direction

The app should feel similar to the reference image:

- Warm Kenyan visual identity.
- Large relationship dashboard cards.
- Soft cream background.
- Coral, olive green, charcoal, and small Kenyan flag-inspired accents.
- Rounded widget-style cards.
- Simple, emotional, not corporate.
- More like a private couple space than a dating app.

Important distinction:

- **Home screen widgets** are what the user sees outside the app.
- **The app layout** is where users configure, send, reply, view history, and manage the relationship space.

## 2. Main App Navigation

Use bottom navigation with 4 tabs:

1. **Home**
   - Main couple dashboard.
   - Mood, doodle, snap, distance, countdown, daily prompt.

2. **Moments**
   - Notes, snaps, doodles, memories, scrapbook.

3. **Prompts**
   - Daily questions, quizzes, answers, streaks.

4. **Us**
   - Partner profile, pairing, widgets, privacy, subscription, settings.

## 3. Home Screen Layout

### Header

Content:

- App name: `Connect`, `Tuko`, or selected final name.
- Small Kenya heart/brand icon.
- Live status: `Live · Synced just now`.
- Partner avatars.
- Online indicators.

Purpose:

Show the relationship is connected and alive.

### Card Grid

Use a two-column card grid.

Cards:

1. **Mood**
   - Large coral card.
   - Partner mood: `Amina is stressed`.
   - Emoji mood options.
   - Main action: `Send love`.

2. **Doodle**
   - Olive green card.
   - Latest doodle preview.
   - Status pill: `Live`.
   - Tap opens doodle composer.

3. **Snap**
   - Dark card.
   - Latest photo preview.
   - Camera action button.
   - Tap opens snap viewer.

4. **Distance**
   - Cream card.
   - Approximate partner distance.
   - Use privacy-safe copy: `12 km apart`.

5. **Countdown**
   - Green card.
   - Event countdown.
   - Example: `3 days to date night`.

6. **Daily Prompt**
   - Cream/coral card.
   - Bilingual question.
   - Example: `Umenimiss? What made you smile today?`

### Premium Banner

At the bottom of the dashboard:

- `Stronger together`
- `Premium for deeper connection`
- `KES 100/mo`

This should be visible but not annoying.

## 4. Moments Screen

Purpose:

Show shared relationship history.

Sections:

- Latest notes.
- Doodles.
- Snaps.
- Memory scrapbook.
- Important dates.

Layout:

- Timeline grouped by date.
- Cards for each moment.
- Filters: `All`, `Notes`, `Snaps`, `Doodles`, `Prompts`.

## 5. Prompts Screen

Purpose:

Relationship-deepening content.

Sections:

- Today's prompt.
- Partner answer status.
- Prompt categories.
- Couple quizzes.
- Streak.

Prompt categories:

- Fun.
- Appreciation.
- Future.
- Intimacy.
- Adventure.
- Family.
- Faith/values if needed.

## 6. Us Screen

Purpose:

Relationship and app settings.

Sections:

- Couple profile.
- Partner connection status.
- Widget setup.
- Privacy controls.
- Distance settings.
- Notification settings.
- Subscription.
- Disconnect/block/report.

Important privacy controls:

- Approximate distance only.
- Share distance on/off.
- Save mood history on/off.
- AI suggestions on/off.
- Quiet hours.

## 7. Primary User Flows

### Send Mood

1. User opens app or taps widget.
2. Selects mood.
3. Adds optional note.
4. Sends.
5. Partner widget updates.

### Send Doodle

1. User taps Doodle card.
2. Doodle composer opens.
3. User draws.
4. Sends.
5. Partner sees doodle in app and widget preview.

### Send Snap

1. User taps Snap card.
2. Camera opens.
3. User takes photo.
4. Optional caption.
5. Sends to partner.

### Reply to Prompt

1. User taps Daily Prompt card.
2. Prompt detail opens.
3. User answers.
4. Partner receives answer notification.
5. Both answers appear once both have replied.

## 8. Visual System

### Colors

Recommended palette:

```text
Cream background: #fff5e8
Coral primary:    #e76f5c
Olive green:      #8fa17d
Deep green:       #2f5f3d
Charcoal:         #262420
Soft border:      #ead8c7
Gold accent:      #d6a348
```

### Typography

Use a friendly rounded sans-serif:

- Flutter: `Inter`, `Nunito Sans`, or `SF Pro` style.
- Headings: bold and large.
- Card labels: medium weight.
- Avoid tiny text.

### Card Rules

- Border radius: 18-24px in the mobile app.
- Widget cards can be slightly smaller radius on actual home screen widgets.
- Use subtle shadows.
- Keep each card focused on one action.

## 9. Recommended Flutter Screen Structure

```text
HomeScreen
  AppHeader
  LiveStatusPill
  PartnerAvatars
  DashboardGrid
    MoodCard
    DoodleCard
    SnapCard
    DistanceCard
    CountdownCard
    DailyPromptCard
  PremiumBanner
  BottomNavigation

MomentsScreen
  MomentFilterTabs
  MomentTimeline

PromptsScreen
  TodayPromptCard
  PromptCategories
  QuizCards
  StreakCard

UsScreen
  CoupleProfileCard
  WidgetSetupCard
  PrivacySettings
  SubscriptionCard
```

## 10. MVP Screens to Design First

1. Onboarding screen.
2. Login/register screen.
3. Pair partner screen.
4. Home dashboard screen.
5. Mood send screen.
6. Doodle composer screen.
7. Snap sender screen.
8. Widget setup screen.
9. Privacy settings screen.
10. Subscription screen.

## 11. Final Layout Recommendation

The app can look very close to the reference image, but it should be slightly cleaner for real use.

The reference design is strong because:

- It shows all core features in one glance.
- It feels emotional and local.
- It makes the app look premium.
- It visually connects app cards with home-screen widgets.

Use that dashboard as the main **Home** tab, then keep deeper features in tabs.

