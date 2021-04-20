<?php

namespace App\Http\Services;

use App\Models\Word;

class FormTextService
{
    private $text;

    public function __construct($text)
    {
        $this->text = $text;
    }

    private function getSeparators() {
        return [
            'open' => ['[', '{', '('],
            'close' => [']', '}', ')'],
        ];
    }

    private function isOpenSeparator($value) {
        return in_array($value, $this->getSeparators()['open']);
    }

    private function getInvertSeparator(string $value, string $type = 'open' ): string
    {
        $separators = $this->getSeparators();
        $index = array_search($value, $separators[$type]);
        $t = $type === 'open' ? 'close' : 'open';
        return $separators[$t][$index];
    }


    private function validateSeparatorsCount(string $str): bool
    {
        $separators = $this->getSeparators();
        $res = true;

        foreach ($separators['open'] as $index => $separator) {
            if(substr_count($str, $separator) !== substr_count($str, $separators['close'][$index])) {
                $res = false;
                break;
            }
        }

        return $res;
    }



    public function validate() {
        $text = $this->text;

        if(strlen($text) < 3) {
            return 'Ошибка, текст не передан';
        }

        $separateSymbols = preg_replace('/[^\{\}\(\)\[\]]/', '', $text);

        if(!$this->isOpenSeparator($separateSymbols[0]))
        {
            return 'Ошибка вадидации, закрывающийся символ раньше открывающегося';
        }

        for ($i = 0; $i < strlen($separateSymbols); $i++) {
            $char = $separateSymbols[$i];

            if($this->isOpenSeparator($char)) {

                $j = $i;

                $closeChar = $this->getInvertSeparator($char);

                for ($countRepeatChar = 0; $j < strlen($separateSymbols); $j++) {

                    if ($separateSymbols[$j] === $char) { // если точно такой же открывающийся символ
                        $countRepeatChar++; // увеличиваем число повторений
                    } else if ($separateSymbols[$j] === $closeChar) { // если нашли закрывающий символ
                        if ($countRepeatChar === 0) {
                            break; // если число повторений 0 - выйти из цикла
                        } else {
                            $countRepeatChar--; // иначе уменьшаем число повторений (исключение закр и откр символов)
                        }
                    }
                }

                $str = substr($separateSymbols, $i, $j - $i);
                $i = $j;
                if(!$this->validateSeparatorsCount($str)) {
                    return 'Ошибка валидации. Нарушена вложенность';
                }
            }
        }
        $depths = $this->getUniqueDepths();
        for($i = 0; $i < count($depths); $i++) {
            $count = 0;

            foreach ($depths[$i] as $value) {
                if($i > 0 && in_array($value, $depths[$i - 1])) {
                    $count++;
                    if($count >= 3) {
                        break;
                    }
                }
            }

            if($i > 0 && $count < 3) {
                return 'Ошибка. В каждом уровне вложенности должно быть хотябы 3 слова с предыдущего уровня (0 уровень тоже считается)';
            }

        }
    }

    private function getStringsArray($text)
    {
        $openSeparators = ['{', '[', '('];
        $closeSeparators = ['}', ']', ')'];

        $array = [];

        for ($i = 0; $i < strlen($text); $i++) {

            $char = $text[$i];

            if (in_array($char, $openSeparators)) { // если тег открывающийся ищем закрывающийся
                $j = $i + 1;
                $closeChar = $closeSeparators[array_search($char, $openSeparators)];

                $countRepeatChar = 0; // число повторений открывающегося символа
                for (; $j < strlen($text); $j++) {

                    if ($text[$j] === $char) { // если точно такой же открывающийся символ
                        $countRepeatChar++; // увеличиваем число повторений
                    } else if ($text[$j] === $closeChar) { // если нашли символ
                        if ($countRepeatChar === 0) {
                            break; // если число повторений 0 - выйти из цикла
                        } else {
                            $countRepeatChar--; // иначе уменьшаем число повторений (исключение закр и откр символов)
                        }
                    }
                }

                $str = substr($text, $i + 1, $j - $i - 1);
                $array[] = $str;
                $i = $j;
            }
        }

        return $array;
    }

    private function getDepths()
    {
        $text = $this->text;
        $depths = [];

        while (preg_match('/[\{\}\[\]\(\)]+/', $text)) {
            $array = $this->getStringsArray($text);
            $depths [] = $array;
            $text = implode(' ', $array);
        }

        return $depths;
    }

    private function separateValues($text)
    {
        $text = preg_replace('/[!\.\,\(\)?\[\]\{\}]/', ' ', $text);
        $text = trim(preg_replace('/\s+/', ' ', $text));
        return explode(' ', $text);
    }

    public function getUniqueDepths()
    {
        $depths = $this->getDepths();

        $unique = [];

        foreach ($depths as $index => $depth) {
            $unique[$index] = [];
            foreach ($depth as $val) {

                if (preg_match('/[\{\}\(\)\[\]]+/', $val)) {
                    $values = $this->separateValues(trim(preg_replace('/[\{\[\(].*[\}\]\)]/', '', $val)));

                    foreach ($values as $value) {
                        if (!in_array($value, $unique[$index]) && strlen($value) > 0) {
                            $unique[$index][] = $value;
                        }
                    }

                } else {
                    $values = $this->separateValues($val);

                    foreach ($values as $value) {
                        if (!in_array($value, $unique[$index]) && strlen($value) > 0) {
                            $unique[$index][] = $value;
                        }
                    }
                }
            }
        }

        return $unique;
    }

    public function addNewWords()
    {
        $depths = $this->getUniqueDepths();

        foreach ($depths as $depthName => $depth) {

            foreach ($depth as $word) {

                $oldWord = Word::where([
                    'value' => $word,
                    'depth' => $depthName
                ])->first();

                try {
                    if ($oldWord !== null) {

                        $oldWord->count++;
                        $oldWord->save();

                    } else {
                        $newWord = new Word();

                        $newWord->fill([
                            'value' =>  $word,
                            'depth' => $depthName,
                            'count' => 0
                        ]);

                        $newWord->save();
                    }

                } catch (\Exception $e) {
                    return [
                        'error' => true,
                        'message'=> $e->getMessage()
                    ];
                }
            }
        }

        return ['error' => false, 'message' => 'Новые слова успешно добавлены'];
    }

}
