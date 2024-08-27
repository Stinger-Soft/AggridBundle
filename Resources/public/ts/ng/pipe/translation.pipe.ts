import { Pipe, PipeTransform } from '@angular/core';
import type { BazingaTranslator } from 'bazinga-translator';

declare var Translator: BazingaTranslator;
/*
*/
@Pipe({name: 'stinger_trans'})
export class TranslationPipe implements PipeTransform {
    transform(key: string, args?: object, domain?: string): string {
        return Translator.trans(key, args, domain);
    }
}