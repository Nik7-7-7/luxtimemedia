<?php

const AIKIT_COMMENTS_PROMPTS = [
    'en' => [
        'prompt' => "Imagine you are a reader of the following article. Write a VERY SHORT reader comment about it. You can agree, disagree, or add something new to the article if you want: \n[[text]] \n\n Comment: ",
    ],
    'th' => [
        'prompt' => "จินตนาการว่าคุณเป็นผู้อ่านของบทความต่อไปนี้ และเขียนความคิดเห็นของผู้อ่านที่สั้นมากเกี่ยวกับมัน คุณสามารถเห็นด้วย ไม่เห็นด้วย หรือเพิ่มสิ่งใหม่เข้าไปในบทความหากคุณต้องการ: \n[[text]] \n\n ความคิดเห็น: ",
    ],
    'he' => [
        'prompt' => "תדמיינו שאתם קוראים את המאמר הבא. כתבו תגובת קורא קצרה מאוד על זה. אתה יכול להסכים, להתנגד או להוסיף משהו חדש למאמר אם ברצונך. \n[[text]] \n\n תגובה: ",
    ],
    'lt' => [
        'prompt' => "Įsivaizduokite, kad esate šio straipsnio skaitytojas. Parašykite labai trumpą skaitytojo komentarą apie tai. Jei norite, galite sutikti, nesutikti arba pridėti kažką naujo prie straipsnio: \n[[text]] \n\n Komentaras: ",
    ],

    'de' => [
        'prompt' => "Stellen Sie sich vor, Sie sind ein Leser des folgenden Artikels. Schreiben Sie einen SEHR KURZEN Leserkommentar dazu. Sie können zustimmen, widersprechen oder dem Artikel etwas Neues hinzufügen, wenn Sie möchten: \n[[text]] \n\n Kommentar: ",
    ],
    'fr' => [
        'prompt' => "Imaginez que vous êtes un lecteur de l'article suivant. Écrivez un commentaire de lecteur TRÈS COURT à ce sujet. Vous pouvez être d'accord, en désaccord ou ajouter quelque chose de nouveau à l'article si vous le souhaitez: \n[[text]] \n\n Commentaire: ",
    ],

    'es' => [
        'prompt' => "Imagínese que es un lector del siguiente artículo. Escriba un comentario de lector MUY CORTO al respecto. Puede estar de acuerdo, en desacuerdo o agregar algo nuevo al artículo si lo desea: \n[[text]] \n\n Comentario: ",
    ],

    'it' => [
        'prompt' => "Immagina di essere un lettore del seguente articolo. Scrivi un commento di lettore MOLTO CORTO a riguardo. Se vuoi, puoi essere d'accordo, in disaccordo o aggiungere qualcosa di nuovo all'articolo: \n[[text]] \n\n Commento: ",
    ],

    'pt' => [
        'prompt' => "Imagine que você é um leitor do seguinte artigo. Escreva um comentário de leitor MUITO CURTO sobre isso. Você pode concordar, discordar ou adicionar algo novo ao artigo, se quiser: \n[[text]] \n\n Comentário: ",

    ],
    'nl' => [
        'prompt' => "Stel je voor dat je een lezer bent van het volgende artikel. Schrijf er een HEEL KORT lezerscommentaar over. Je kunt het ermee eens zijn, het er niet mee eens zijn of iets nieuws aan het artikel toevoegen als je dat wilt: \n[[text]] \n\n Commentaar: ",

    ],
    'pl' => [
        'prompt' => "Wyobraź sobie, że jesteś czytelnikiem następującego artykułu. Napisz o tym BARDZO KRÓTKI komentarz czytelnika. Jeśli chcesz, możesz się zgodzić, nie zgodzić lub dodać coś nowego do artykułu: \n[[text]] \n\n Komentarz: ",

    ],
    'ru' => [
        'prompt' => "Представьте, что вы читатель следующей статьи. Напишите ОЧЕНЬ КОРОТКИЙ комментарий читателя об этом. Если хотите, можете согласиться, не согласиться или добавить что-то новое к статье: \n[[text]] \n\n Комментарий: ",
    ],
    'ja' => [
        'prompt' => "次の記事の読者であると想像してください。それについての非常に短い読者コメントを書いてください。同意する、同意しない、または記事に新しいことを追加することができます。 \n[[text]] \n\n コメント: ",

    ],
    'zh' => [
        'prompt' => "想象一下，您是以下文章的读者。请写一篇非常简短的读者评论。如果您愿意，可以同意，不同意或添加一些新内容。 \n[[text]] \n\n 评论: ",

    ],
    'br' => [
        'prompt' => "Imagine que você é um leitor do seguinte artigo. Escreva um comentário de leitor MUITO CURTO sobre isso. Você pode concordar, discordar ou adicionar algo novo ao artigo, se quiser: \n[[text]] \n\n Comentário: ",

    ],
    'tr' => [
        'prompt' => "Aşağıdaki makalenin bir okuyucusu olduğunuzu hayal edin. Bunu hakkında ÇOK KISA bir okuyucu yorumu yazın. İsterseniz makaleye katılabilir, katılmayabilir veya yeni bir şey ekleyebilirsiniz: \n[[text]] \n\n Yorum: ",

    ],
    'ar' => [
        'prompt' => "تخيل أنك قارئ للمقال التالي. اكتب تعليق قارئ قصير جدًا حول هذا. يمكنك الموافقة أو الاختلاف أو إضافة شيء جديد إلى المقال إذا كنت ترغب في ذلك: \n[[text]] \n\n تعليق: ",

    ],
    'ko' => [
        'prompt' => "다음 기사의 독자라고 상상해보십시오. 그에 대한 매우 짧은 독자 댓글을 작성하십시오. 원한다면 동의하거나 동의하지 않거나 기사에 새로운 내용을 추가 할 수 있습니다: \n[[text]] \n\n 댓글: ",

    ],

    'hi' => [
        'prompt' => "अगले लेख के पाठक होने की कल्पना करें। इसके बारे में एक बहुत ही छोटा पाठक टिप्पणी लिखें। आप सहमत हो सकते हैं, असहमत हो सकते हैं या अगर आप चाहें तो लेख में कुछ नया जोड़ सकते हैं। \n[[text]] \n\n टिप्पणी: ",

    ],
    'id' => [
        'prompt' => "Bayangkan Anda adalah pembaca artikel berikut. Tulis komentar pembaca yang SANGAT SINGKAT tentang hal itu. Anda dapat setuju, tidak setuju, atau menambahkan sesuatu yang baru ke artikel jika Anda mau: \n[[text]] \n\n Komentar: ",

    ],
    'sv' => [
        'prompt' => "Tänk dig att du är en läsare av följande artikel. Skriv en MYCKET KORT läsarkommentar om det. Du kan hålla med, inte hålla med eller lägga till något nytt i artikeln om du vill: \n[[text]] \n\n Kommentar: ",

    ],
    'da' => [
        'prompt' => "Forestil dig, at du er en læser af følgende artikel. Skriv en MEGET KORT læserkommentar om det. Du kan være enig, uenig eller tilføje noget nyt til artiklen, hvis du vil: \n[[text]] \n\n Kommentar: ",

    ],
    'fi' => [
        'prompt' => "Kuvittele olevasi seuraavan artikkelin lukija. Kirjoita siitä Erittäin lyhyt lukijan kommentti. Voit olla samaa mieltä, eri mieltä tai lisätä jotain uutta artikkeliin, jos haluat: \n[[text]] \n\n Kommentti: ",

    ],
    'no' => [
        'prompt' => "Forestill deg at du er en leser av følgende artikkel. Skriv en VELDIG KORT leserkommentar om det. Du kan være enig, uenig eller legge til noe nytt i artikkelen hvis du vil: \n[[text]] \n\n Kommentar: ",

    ],
    'ro' => [
        'prompt' => "Imaginați-vă că sunteți un cititor al următorului articol. Scrieți un comentariu de cititor FOARTE SCURT despre asta. Puteți fi de acord, să nu fiți de acord sau să adăugați ceva nou la articol, dacă doriți: \n[[text]] \n\n Comentariu: ",

    ],
    'ka' => [
        'prompt' => "გააგრძელეთ, რომ ხართ შემდეგი სტატიის წინამდებარე. დაწერეთ მარტივი წამყვანი წამყვანი კომენტარი მას შესახებ. თუ გსურთ, შეგიძლიათ თანამედროვე იყოთ, არ იყოთ თანამედროვე ან რამეს ახალი დაამატოთ სტატიაში, თუ გსურთ: \n[[text]] \n\n კომენტარი: ",

    ],
    'vi' => [
        'prompt' => "Hãy tưởng tượng bạn là một người đọc của bài báo sau. Hãy viết một bình luận ngắn về người đọc về nó. Bạn có thể đồng ý, không đồng ý hoặc thêm một cái gì đó mới vào bài báo nếu bạn muốn: \n[[text]] \n\n Nhận xét: ",

    ],
    'hu' => [
        'prompt' => "Képzelje el, hogy az alábbi cikk olvasója. Írjon róla EGY NAGYON RÖVID olvasói megjegyzést. Ha akarja, egyetért, nem ért egyet, vagy újat adhat hozzá a cikkhez: \n[[text]] \n\n Megjegyzés: ",

    ],
    'bg' => [
        'prompt' => "Представете си, че сте читател на следващата статия. Напишете много кратък коментар на читателя за това. Можете да се съгласите, да не се съгласите или да добавите нещо ново към статията, ако искате: \n[[text]] \n\n Коментар: ",

    ],
    'el' => [
        'prompt' => "Φανταστείτε ότι είστε αναγνώστης του ακόλουθου άρθρου. Γράψτε ένα ΠΟΛΥ ΚΟΝΤΟ σχόλιο αναγνώστη για αυτό. Μπορείτε να συμφωνήσετε, να διαφωνήσετε ή να προσθέσετε κάτι νέο στο άρθρο αν θέλετε: \n[[text]] \n\n Σχόλιο: ",

    ],
    'fa' => [
        'prompt' => "تصور کنید شما خواننده مقاله زیر هستید. یک نظر کوتاه خواننده در مورد آن بنویسید. اگر بخواهید، می توانید موافق باشید، مخالف باشید یا چیزی جدید به مقاله اضافه کنید. \n[[text]] \n\n نظر: ",

    ],
    'sk' => [
        'prompt' => "Predstavte si, že ste čitateľom nasledujúceho článku. Napíšte o tom VEĽMI KRÁTKY čitateľský komentár. Ak chcete, môžete súhlasiť, nesúhlasiť alebo pridať niečo nové k článku: \n[[text]] \n\n Komentár: ",

    ],
    'cs' => [
        'prompt' => "Představte si, že jste čtenářem následujícího článku. Napište o tom VELMI KRÁTKÝ čtenářský komentář. Pokud chcete, můžete souhlasit, nesouhlasit nebo přidat něco nového k článku: \n[[text]] \n\n Komentář: ",

    ],
    'ca' => [
        'prompt' => "Imagina que ets un lector de l'article següent. Escriu un comentari de lector MOLT CURT sobre això. Pots estar d'acord, en desacord o afegir alguna cosa nova a l'article si vols: \n[[text]] \n\n Comentari: ",
    ],
    'hr' => [
        'prompt' => "Zamislite da ste čitatelj sljedećeg članka. Napišite vrlo kratak čitateljski komentar o tome. Možete se složiti, ne složiti se ili dodati nešto novo u članak ako želite: \n[[text]] \n\n Komentar: ",

    ],
    'uk' => [
        'prompt' => "Уявіть собі, що ви читач наступної статті. Напишіть ДУЖЕ КОРОТКИЙ коментар читача про це. Якщо хочете, можете погодитися, не погодитися або додати щось нове до статті: \n[[text]] \n\n Коментар: ",

    ],
];
