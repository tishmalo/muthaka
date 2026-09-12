<?php

namespace Database\Seeders;

use App\Models\Prompt;
use Illuminate\Database\Seeder;

class PromptSeeder extends Seeder
{
    /**
     * Seed the daily-prompt library (95 prompts across 6 categories).
     *
     * Categories + emoji match the Android Prompts tab (Fun 😄 / Appreciation 💛 /
     * Future 🔮 / Intimacy 💞 / Adventure 🌍 / Family 🏡). Rotation to a fresh prompt
     * each day is handled by the `prompts:dispatch-daily` command, not here.
     *
     * Idempotent: keyed on content so re-running never creates duplicates or
     * deletes answers (prompt_answers cascade off prompts).
     */
    public function run(): void
    {
        $prompts = [
            // Fun 😄 (24)
            ['fun', '😄', 'What inside joke never fails to make you laugh?'],
            ['fun', '😄', 'What is the most ridiculous thing we have ever done together?'],
            ['fun', '😄', 'What song instantly makes you want to dance — and am I invited to the show?'],
            ['fun', '😄', 'If we swapped lives for a day, what would you want me to do first?'],
            ['fun', '😄', 'Which one of us is more likely to get lost — and who rescues whom?'],
            ['fun', '😄', 'What is a dream date you would plan if money were not an issue?'],
            ['fun', '😄', 'What is the silliest nickname you have ever given me?'],
            ['fun', '😄', 'If our relationship had a theme song, which one is it and why?'],
            ['fun', '😄', 'What is the funniest text you have ever sent me?'],
            ['fun', '😄', 'What animal represents me and why? (Be honest!)'],
            ['fun', '😄', 'If we opened a business together, what would we sell?'],
            ['fun', '😄', 'What is a meal we should learn to cook together?'],
            ['fun', '😄', 'What kind of road trip is a must for us?'],
            ['fun', '😄', 'What is your favourite memory of us from the last month?'],
            ['fun', '😄', 'If you had a superpower for one day, what would you do with me?'],
            ['fun', '😄', 'What would our pet\'s name be if we got one?'],
            ['fun', '😄', 'What is the most embarrassing thing I have done that you love me for anyway?'],
            ['fun', '😄', 'If we became famous, what would our couple name be?'],
            ['fun', '😄', 'What game do you want to challenge me at next?'],
            ['fun', '😄', 'What is a hobby you want to pick up together?'],
            ['fun', '😄', 'What fictional couple are we most like?'],
            ['fun', '😄', 'What is a "you thing" I do that always cheers you up?'],
            ['fun', '😄', 'If we had a free weekend with zero plans, what would we do?'],
            ['fun', '😄', 'What is the best compliment you have ever received — from me or anyone?'],

            // Appreciation 💛 (18)
            ['appreciation', '💛', 'What is something small I do that makes your day better?'],
            ['appreciation', '💛', 'What quality in me are you most grateful for today?'],
            ['appreciation', '💛', 'When did you last feel truly proud of me?'],
            ['appreciation', '💛', 'What is a moment we shared that you replay in your head?'],
            ['appreciation', '💛', 'What do I say or do that instantly calms you down?'],
            ['appreciation', '💛', 'Which of my habits do you secretly admire?'],
            ['appreciation', '💛', 'What is the kindest thing I have ever done for you?'],
            ['appreciation', '💛', 'What part of me do you hope our future kids take after?'],
            ['appreciation', '💛', 'What have I taught you about love?'],
            ['appreciation', '💛', 'When do you feel safest with me?'],
            ['appreciation', '💛', 'What is a sacrifice you have seen me make that you appreciated?'],
            ['appreciation', '💛', 'What about the way I love you feels different?'],
            ['appreciation', '💛', 'What is one thing about our relationship you would never change?'],
            ['appreciation', '💛', 'What do you thank me for most often?'],
            ['appreciation', '💛', 'How do I make you feel about yourself when we are together?'],
            ['appreciation', '💛', 'What is a small win of ours you want to celebrate?'],
            ['appreciation', '💛', 'What is an apology I gave that meant a lot to you?'],
            ['appreciation', '💛', 'What do you love most about how we handle disagreements?'],

            // Future 🔮 (15)
            ['future', '🔮', 'Where do you see us in five years?'],
            ['future', '🔮', 'What is the first trip we should take together when we can?'],
            ['future', '🔮', 'What kind of home do you dream of building with me?'],
            ['future', '🔮', 'What is a goal we should chase together this year?'],
            ['future', '🔮', 'If we could plan one big celebration, what would it be?'],
            ['future', '🔮', 'What skill do you want to master before we are old and grey?'],
            ['future', '🔮', 'What tradition do you want to start with me?'],
            ['future', '🔮', 'What is something you hope we never stop doing?'],
            ['future', '🔮', 'What does "making it" look like for us?'],
            ['future', '🔮', 'What is a place you want to live in one day, even briefly?'],
            ['future', '🔮', 'What future "us" moment are you most looking forward to?'],
            ['future', '🔮', 'What boring-but-important thing should we sort out soon?'],
            ['future', '🔮', 'What dream from before we met still lives on in you?'],
            ['future', '🔮', 'If we saved up for one big thing, what would it be?'],
            ['future', '🔮', 'What is one promise you want to make me for the future?'],

            // Intimacy 💞 (12)
            ['intimacy', '💞', 'When do you feel closest to me?'],
            ['intimacy', '💞', 'What is a small touch from me that you remember?'],
            ['intimacy', '💞', 'How do you know when I need you, even before I say it?'],
            ['intimacy', '💞', 'What is the most vulnerable you have ever felt with me — and what did I do?'],
            ['intimacy', '💞', 'What makes you feel seen by me?'],
            ['intimacy', '💞', 'What is one thing you have never told me but have wanted to?'],
            ['intimacy', '💞', 'What does intimacy look like on a busy day for us?'],
            ['intimacy', '💞', 'When was the last time you felt truly connected to me?'],
            ['intimacy', '💞', 'What is a word that best describes how I make you feel?'],
            ['intimacy', '💞', 'What do you need from me when you are having a hard day?'],
            ['intimacy', '💞', 'What is a boundary you appreciate that we keep?'],
            ['intimacy', '💞', 'What moment with me made you feel completely yourself?'],

            // Adventure 🌍 (16)
            ['adventure', '🌍', 'What is one place in Kenya you still want to explore with me?'],
            ['adventure', '🌍', 'Beach or highlands — where should our next getaway be?'],
            ['adventure', '🌍', 'What is an adventure you are scared to try but would with me?'],
            ['adventure', '🌍', 'If we had one free day in Nairobi, what would we do?'],
            ['adventure', '🌍', 'What is the best hike or road trip we should plan?'],
            ['adventure', '🌍', 'What is a food neither of us has tried but should?'],
            ['adventure', '🌍', 'What is a concert or festival we should do together once?'],
            ['adventure', '🌍', 'Morning person or night owl — and what is our next sunrise or midnight plan?'],
            ['adventure', '🌍', 'What is a city you want to get lost in with me?'],
            ['adventure', '🌍', 'What is the boldest thing we have done so far as a couple?'],
            ['adventure', '🌍', 'What is a weekend hobby-cation we could take?'],
            ['adventure', '🌍', 'What natural wonder should we see before we are 40?'],
            ['adventure', '🌍', 'What is a small adventure we could do this very weekend?'],
            ['adventure', '🌍', 'What is a country you would love to ring in a new year from?'],
            ['adventure', '🌍', 'What is a skill we should learn to enable our dream trip together?'],
            ['adventure', '🌍', 'If we had a bucket-list day together, what is on it?'],

            // Family 🏡 (10)
            ['family', '🏡', 'What family tradition of yours should we carry forward?'],
            ['family', '🏡', 'How do you imagine our first holiday season together?'],
            ['family', '🏡', 'What does "home" mean to you when you think of us?'],
            ['family', '🏡', 'Who would we invite to our first big family dinner?'],
            ['family', '🏡', 'What is a family recipe you would want me to learn?'],
            ['family', '🏡', 'How do you want to handle holidays between our families?'],
            ['family', '🏡', 'What is a childhood memory of yours that shaped you?'],
            ['family', '🏡', 'What makes a Sunday feel like family time to you?'],
            ['family', '🏡', 'What part of your family\'s culture do you want our home to keep?'],
            ['family', '🏡', 'If we could host one family gathering a year, what would it look like?'],
        ];

        foreach ($prompts as [$category, $emoji, $content]) {
            Prompt::firstOrCreate(
                ['content' => $content],
                [
                    'category' => $category,
                    'emoji' => $emoji,
                    'type' => 'daily',
                    'is_active' => true,
                ]
            );
        }

        $this->command?->info('Seeded '.count($prompts).' daily prompts.');
    }
}