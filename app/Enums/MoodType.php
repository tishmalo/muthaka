<?php

namespace App\Enums;

enum MoodType: string
{
    case HAPPY = 'happy';
    case SAD = 'sad';
    case STRESSED = 'stressed';
    case CALM = 'calm';
    case EXCITED = 'excited';
    case ANGRY = 'angry';
    case TIRED = 'tired';
    case LOVED = 'loved';
    case GRATEFUL = 'grateful';
    case WORRIED = 'worried';

    public function emoji(): string
    {
        return match($this) {
            self::HAPPY => '😊',
            self::SAD => '😢',
            self::STRESSED => '😰',
            self::CALM => '😌',
            self::EXCITED => '🤩',
            self::ANGRY => '😡',
            self::TIRED => '😴',
            self::LOVED => '🥰',
            self::GRATEFUL => '🙏',
            self::WORRIED => '😟',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::HAPPY => '#FFD93D',
            self::SAD => '#6C5B7B',
            self::STRESSED => '#F08A5D',
            self::CALM => '#7EC8E3',
            self::EXCITED => '#FF6B6B',
            self::ANGRY => '#E74C3C',
            self::TIRED => '#A8A8A8',
            self::LOVED => '#FF85A2',
            self::GRATEFUL => '#F9ED69',
            self::WORRIED => '#95B8D1',
        };
    }

    public function label(): string
    {
        return match($this) {
            self::HAPPY => 'Happy',
            self::SAD => 'Sad',
            self::STRESSED => 'Stressed',
            self::CALM => 'Calm',
            self::EXCITED => 'Excited',
            self::ANGRY => 'Angry',
            self::TIRED => 'Tired',
            self::LOVED => 'Loved',
            self::GRATEFUL => 'Grateful',
            self::WORRIED => 'Worried',
        };
    }
}