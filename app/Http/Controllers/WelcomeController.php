<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WelcomeController extends Controller
{
    public function show()
    {
        if (session('welcome_seen')) {
            return redirect()->route('tracker.index');
        }

        return view('welcome.index', [
            'quote' => $this->fetchQuote(),
        ]);
    }

    public function continue(Request $request)
    {
        $request->session()->put('welcome_seen', true);

        return redirect()->route('tracker.index');
    }

    private function fetchQuote(): array
    {
        $providers = [
            fn () => $this->fromZenQuotes(),
            fn () => $this->fromQuotable(),
        ];

        foreach ($providers as $provider) {
            try {
                $quote = $provider();
                if ($quote) {
                    return $quote;
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $this->fallbackQuote();
    }

    private function fromZenQuotes(): ?array
    {
        $response = Http::timeout(5)
            ->withHeaders(['User-Agent' => 'RADiiX-INFINITEii-Tracker/1.0'])
            ->get('https://zenquotes.io/api/random');

        if (!$response->successful()) {
            return null;
        }

        $row = $response->json()[0] ?? null;
        if (empty($row['q'])) {
            return null;
        }

        return [
            'text' => $row['q'],
            'author' => $row['a'] ?? 'Unknown',
        ];
    }

    private function fromQuotable(): ?array
    {
        $response = Http::timeout(4)->get('https://api.quotable.io/random', [
            'tags' => 'inspirational|wisdom|success|motivational',
        ]);

        if (!$response->successful()) {
            return null;
        }

        $data = $response->json();
        if (empty($data['content'])) {
            return null;
        }

        return [
            'text' => $data['content'],
            'author' => $data['author'] ?? 'Unknown',
        ];
    }

    private function fallbackQuote(): array
    {
        $fallbacks = [
            ['text' => 'Rooting Intelligence, Inspiring Innovation — excellence is built one thoughtful step at a time.', 'author' => 'RADiiX INFINITEii'],
            ['text' => 'Success is not final, failure is not fatal: it is the courage to continue that counts.', 'author' => 'Winston Churchill'],
            ['text' => 'The only way to do great work is to love what you do.', 'author' => 'Steve Jobs'],
            ['text' => 'Quality is not an act, it is a habit.', 'author' => 'Aristotle'],
            ['text' => 'Opportunities don\'t happen. You create them.', 'author' => 'Chris Grosser'],
        ];

        return $fallbacks[array_rand($fallbacks)];
    }
}
