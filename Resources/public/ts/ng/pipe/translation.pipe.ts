import { Pipe, PipeTransform } from '@angular/core';
import type { BazingaTranslator } from 'bazingajstranslation/js/translator.min.js';

declare var Translator: BazingaTranslator;
/*
*/
@Pipe({name: 'stinger_trans'})
export class TranslationPipe implements PipeTransform {
    transform(key: number, args?: object, domain?: string): string {
        return Translator.trans(key, args, domain);
    }
}