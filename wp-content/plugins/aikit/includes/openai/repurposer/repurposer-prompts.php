<?php

const AIKIT_REPURPOSER_PROMPTS = [
    'en' => [
        'prompts' => [
            'text-generation' => "Rewrite every part of this text in your own words.\n---\nText:\n[[text]]\n\n---\nRewritten text:",
            'text-generation-with-seo-keywords' => "Rewrite every part of this text in your own words and try to use the following seo keywords when possible: [[keywords]]\n---\nText:\n[[text]]\n\n---\nRewritten text:",
            'image' => "Describe an image that would be best fit for this text:\n[[text]]\n---\nCreative image description in one sentence of 6 words:\n",
            'summary' => "Write a summary of the following text in one sentence:\n[[text]]\n---\nSummary in one sentence:",
            'title' => "Generate a title for an article that discusses the following topics:\n[[summaries]]\n---\nTitle:",
            'title-with-seo-keywords' => "Generate a title for an article that discusses the following text and try to use the following seo keywords when possible:[[keywords]]\n---\nText:\n[[summaries]]\n---\nTitle:",
        ]
    ],
    'th' => [
        'prompts' => [
            'text-generation' => "เขียนข้อความนี้ใหม่ด้วยคำของคุณเอง\n---\nข้อความ:\n[[text]]\n\n---\nข้อความที่เขียนใหม่:",
            'text-generation-with-seo-keywords' => "เขียนข้อความนี้ใหม่ด้วยคำของคุณเองและพยายามใช้คำสำคัญ SEO ต่อไปนี้เมื่อเป็นไปได้: [[keywords]]\n---\nข้อความ:\n[[text]]\n\n---\nข้อความที่เขียนใหม่:",
            'image' => "อธิบายภาพที่เหมาะสมที่สุดสำหรับข้อความนี้:\n[[text]]\n---\nคำอธิบายภาพที่สร้างสรรค์ในประโยคเดียว 6 คำ:",
            'summary' => "เขียนสรุปของข้อความต่อไปนี้ในประโยคเดียว:\n[[text]]\n---\nสรุปในประโยคเดียว:",
            'title' => "สร้างชื่อเรื่องสำหรับบทความที่พูดถึงหัวข้อต่อไปนี้:\n[[summaries]]\n---\nชื่อเรื่อง:",
            'title-with-seo-keywords' => "สร้างชื่อเรื่องสำหรับบทความที่พูดถึงข้อความต่อไปนี้และพยายามใช้คำสำคัญ SEO ต่อไปนี้เมื่อเป็นไปได้:[[keywords]]\n---\nข้อความ:\n[[summaries]]\n---\nชื่อเรื่อง:",
        ]
    ],
    'he' => [
        'prompts' => [
            'text-generation' => "כתוב מחדש כל חלק מהטקסט הזה במילים שלך.\n---\nטקסט:\n[[text]]\n\n---\nטקסט מחדש:",
            'text-generation-with-seo-keywords' => "כתוב מחדש כל חלק מהטקסט הזה במילים שלך ונסה להשתמש במילות המפתח של SEO הבאות כשזה אפשרי: [[keywords]]\n---\nטקסט:\n[[text]]\n\n---\nטקסט מחדש:",
            'image' => "תאר תמונה שתתאים בצורה הטובה ביותר לטקסט הזה:\n[[text]]\n---\nתיאור תמונה יצירתי במשפט אחד של 6 מילים:\n",
            'summary' => "כתוב תקציר של הטקסט הבא במשפט אחד:\n[[text]]\n---\nתקציר במשפט אחד:",
            'title' => "צור כותרת למאמר שמדבר על הנושאים הבאים:\n[[summaries]]\n---\nכותרת:",
            'title-with-seo-keywords' => "צור כותרת למאמר שמדבר על הטקסט הבא ונסה להשתמש במילות המפתח של SEO הבאות כשזה אפשרי:[[keywords]]\n---\nטקסט:\n[[summaries]]\n---\nכותרת:",
        ]
    ],
    'lt' => [
        'prompts' => [
            'text-generation' => "Performuokite šį tekstą savo žodžiais.\n---\nTekstas:\n[[text]]\n\n---\nPerformuotas tekstas:",
            'text-generation-with-seo-keywords' => "Performuokite šį tekstą savo žodžiais ir stenkitės naudoti šiuos SEO raktažodžius, kai tik įmanoma: [[keywords]]\n---\nTekstas:\n[[text]]\n\n---\nPerformuotas tekstas:",
            'image' => "Aprašykite vaizdą, kuris geriausiai tiktų šiam tekstui:\n[[text]]\n---\nKūrybinis vaizdo aprašymas vienoje sakinyje iš 6 žodžių:\n",
            'summary' => "Parašykite šio teksto santrauką vienoje sakinyje:\n[[text]]\n---\nSantrauka vienoje sakinyje:",
            'title' => "Sugeneruokite pavadinimą straipsniui, kuriame aptariami šie dalykai:\n[[summaries]]\n---\nPavadinimas:",
            'title-with-seo-keywords' => "Sugeneruokite pavadinimą straipsniui, kuriame aptariamas šis tekstas ir stenkitės naudoti šiuos SEO raktažodžius, kai tik įmanoma:[[keywords]]\n---\nTekstas:\n[[summaries]]\n---\nPavadinimas:",
        ],
    ],

    'de' => [
        'prompts' => [
            // with Du
            'text-generation' => "Schreibe diesen Text mit deinen eigenen Worten um.\n---\nText:\n[[text]]\n\n---\nUmgewandelter Text:",
            'text-generation-with-seo-keywords' => "Schreibe diesen Text mit deinen eigenen Worten um und versuche dabei die folgenden SEO-Keywords zu verwenden: [[keywords]]\n---\nText:\n[[text]]\n\n---\nUmgewandelter Text:",
            'image' => "Beschreibe ein Bild, das am besten zu diesem Text passt:\n[[text]]\n---\nKreative Bildbeschreibung in einem Satz mit 6 Wörtern:\n",
            'summary' => "Schreibe eine Zusammenfassung des folgenden Textes in einem Satz:\n[[text]]\n---\nZusammenfassung in einem Satz:",
            'title' => "Generiere einen Titel für einen Artikel, der die folgenden Themen behandelt:\n[[summaries]]\n---\nTitel:",
            'title-with-seo-keywords' => "Generiere einen Titel für einen Artikel, der den folgenden Text behandelt und versuche dabei die folgenden SEO-Keywords zu verwenden:[[keywords]]\n---\nText:\n[[summaries]]\n---\nTitel:",
        ],
    ],
    'fr' => [
        'prompts' => [
            'text-generation' => "Réécrivez chaque partie de ce texte avec vos propres mots.\n---\nTexte:\n[[text]]\n\n---\nTexte réécrit:",
            'text-generation-with-seo-keywords' => "Réécrivez chaque partie de ce texte avec vos propres mots et essayez d'utiliser les mots-clés SEO suivants lorsque cela est possible: [[keywords]]\n---\nTexte:\n[[text]]\n\n---\nTexte réécrit:",
            'image' => "Décrivez une image qui conviendrait le mieux à ce texte:\n[[text]]\n---\nDescription créative de l'image en une phrase de 6 mots:\n",
            'summary' => "Rédigez un résumé du texte suivant en une phrase:\n[[text]]\n---\nRésumé en une phrase:",
            'title' => "Générez un titre pour un article qui traite des sujets suivants:\n[[summaries]]\n---\nTitre:",
            'title-with-seo-keywords' => "Générez un titre pour un article qui traite du texte suivant et essayez d'utiliser les mots-clés SEO suivants lorsque cela est possible:[[keywords]]\n---\nTexte:\n[[summaries]]\n---\nTitre:",
        ],
    ],
    'es' => [
        'prompts' => [
            'text-generation' => "Reescribe este texto con tus propias palabras.\n---\nTexto:\n[[text]]\n\n---\nTexto reescrito:",
            'text-generation-with-seo-keywords' => "Reescribe este texto con tus propias palabras y trata de usar las siguientes palabras clave de SEO cuando sea posible: [[keywords]]\n---\nTexto:\n[[text]]\n\n---\nTexto reescrito:",
            'image' => "Describe una imagen que se ajuste mejor a este texto:\n[[text]]\n---\nDescripción creativa de la imagen en una frase de 6 palabras:\n",
            'summary' => "Escribe un resumen del siguiente texto en una frase:\n[[text]]\n---\nResumen en una frase:",
            'title' => "Genera un título para un artículo que trate los siguientes temas:\n[[summaries]]\n---\nTítulo:",
            'title-with-seo-keywords' => "Genera un título para un artículo que trate el siguiente texto y trata de usar las siguientes palabras clave de SEO cuando sea posible:[[keywords]]\n---\nTexto:\n[[summaries]]\n---\nTítulo:",


        ],
    ],
    'it' => [
        'prompts' => [
            'text-generation' => "Riscrivi questo testo con le tue parole.\n---\nTesto:\n[[text]]\n\n---\nTesto riscritto:",
            'text-generation-with-seo-keywords' => "Riscrivi questo testo con le tue parole e cerca di utilizzare le seguenti parole chiave SEO quando possibile: [[keywords]]\n---\nTesto:\n[[text]]\n\n---\nTesto riscritto:",
            'image' => "Descrivi un'immagine che si adatti meglio a questo testo:\n[[text]]\n---\nDescrizione creativa dell'immagine in una frase di 6 parole:\n",
            'summary' => "Scrivi un riassunto del seguente testo in una frase:\n[[text]]\n---\nRiassunto in una frase:",
            'title' => "Genera un titolo per un articolo che tratti i seguenti argomenti:\n[[summaries]]\n---\nTitolo:",
            'title-with-seo-keywords' => "Genera un titolo per un articolo che tratti il seguente testo e cerca di utilizzare le seguenti parole chiave SEO quando possibile:[[keywords]]\n---\nTesto:\n[[summaries]]\n---\nTitolo:",

        ],
    ],
    'pt' => [
        'prompts' => [
            'text-generation' => "Reescreva este texto com suas próprias palavras.\n---\nTexto:\n[[text]]\n\n---\nTexto reescrito:",
            'text-generation-with-seo-keywords' => "Reescreva este texto com suas próprias palavras e tente usar as seguintes palavras-chave de SEO quando possível: [[keywords]]\n---\nTexto:\n[[text]]\n\n---\nTexto reescrito:",
            'image' => "Descreva uma imagem que se encaixe melhor neste texto:\n[[text]]\n---\nDescrição criativa da imagem em uma frase de 6 palavras:\n",
            'summary' => "Escreva um resumo do seguinte texto em uma frase:\n[[text]]\n---\nResumo em uma frase:",
            'title' => "Gere um título para um artigo que trate dos seguintes temas:\n[[summaries]]\n---\nTítulo:",
            'title-with-seo-keywords' => "Gere um título para um artigo que trate do seguinte texto e tente usar as seguintes palavras-chave de SEO quando possível:[[keywords]]\n---\nTexto:\n[[summaries]]\n---\nTítulo:",

        ],
    ],
    'nl' => [
        'prompts' => [
            'text-generation' => "Herschrijf deze tekst met je eigen woorden.\n---\nTekst:\n[[text]]\n\n---\nHerschreven tekst:",
            'text-generation-with-seo-keywords' => "Herschrijf deze tekst met je eigen woorden en probeer de volgende SEO zoekwoorden te gebruiken waar mogelijk: [[keywords]]\n---\nTekst:\n[[text]]\n\n---\nHerschreven tekst:",
            'image' => "Beschrijf een afbeelding die het beste bij deze tekst past:\n[[text]]\n---\nCreatieve beschrijving van de afbeelding in een zin van 6 woorden:\n",
            'summary' => "Schrijf een samenvatting van de volgende tekst in één zin:\n[[text]]\n---\nSamenvatting in één zin:",
            'title' => "Genereer een titel voor een artikel dat de volgende onderwerpen behandelt:\n[[summaries]]\n---\nTitel:",
            'title-with-seo-keywords' => "Genereer een titel voor een artikel dat de volgende tekst behandelt en probeer de volgende SEO zoekwoorden te gebruiken waar mogelijk:[[keywords]]\n---\nTekst:\n[[summaries]]\n---\nTitel:",

        ],
    ],
    'pl' => [
        'prompts' => [
            'text-generation' => "Przepisz ten tekst własnymi słowami.\n---\nTekst:\n[[text]]\n\n---\nPrzepisany tekst:",
            'text-generation-with-seo-keywords' => "Przepisz ten tekst własnymi słowami i spróbuj użyć następujących słów kluczowych SEO, jeśli to możliwe: [[keywords]]\n---\nTekst:\n[[text]]\n\n---\nPrzepisany tekst:",
            'image' => "Opisz obraz, który najlepiej pasuje do tego tekstu:\n[[text]]\n---\nKreatywne opisanie obrazu w jednym zdaniu:\n",
            'summary' => "Napisz podsumowanie następującego tekstu w jednym zdaniu:\n[[text]]\n---\nPodsumowanie w jednym zdaniu:",
            'title' => "Wygeneruj tytuł artykułu, który będzie dotyczył następujących tematów:\n[[summaries]]\n---\nTytuł:",
            'title-with-seo-keywords' => "Wygeneruj tytuł artykułu, który będzie dotyczył następującego tekstu i spróbuj użyć następujących słów kluczowych SEO, jeśli to możliwe:[[keywords]]\n---\nTekst:\n[[summaries]]\n---\nTytuł:",

        ],
    ],
    'ru' => [
        'prompts' => [
            'text-generation' => "Перепишите этот текст своими словами.\n---\nТекст:\n[[text]]\n\n---\nПереписанный текст:",
            'text-generation-with-seo-keywords' => "Перепишите этот текст своими словами и постарайтесь использовать следующие ключевые слова SEO, если это возможно: [[keywords]]\n---\nТекст:\n[[text]]\n\n---\nПереписанный текст:",
            'image' => "Опишите изображение, которое лучше всего подходит к этому тексту:\n[[text]]\n---\nКреативное описание изображения в одном предложении:\n",
            'summary' => "Напишите резюме следующего текста в одном предложении:\n[[text]]\n---\nРезюме в одном предложении:",
            'title' => "Сгенерируйте заголовок статьи, которая будет касаться следующих тем:\n[[summaries]]\n---\nЗаголовок:",
            'title-with-seo-keywords' => "Сгенерируйте заголовок статьи, которая будет касаться следующего текста и постарайтесь использовать следующие ключевые слова SEO, если это возможно:[[keywords]]\n---\nТекст:\n[[summaries]]\n---\nЗаголовок:",
        ],
    ],
    'ja' => [
        'prompts' => [
            'text-generation' => "このテキストを自分の言葉で書き直してください。\n---\nテキスト:\n[[text]]\n\n---\n書き直したテキスト:",
            'text-generation-with-seo-keywords' => "このテキストを自分の言葉で書き直してください。可能な場合は、次のSEOキーワードを使用してください:[[keywords]]\n---\nテキスト:\n[[text]]\n\n---\n書き直したテキスト:",
            'image' => "このテキストに最も適した画像を説明してください:\n[[text]]\n---\n6語で表現された画像のクリエイティブな説明:\n",
            'summary' => "次のテキストの要約を1文で書いてください:\n[[text]]\n---\n1文での要約:",
            'title' => "次のトピックに関する記事のタイトルを生成してください:\n[[summaries]]\n---\nタイトル:",
            'title-with-seo-keywords' => "次のテキストに関する記事のタイトルを生成してください。可能な場合は、次のSEOキーワードを使用してください:[[keywords]]\n---\nテキスト:\n[[summaries]]\n---\nタイトル:",

        ],
    ],
    'zh' => [
        'prompts' => [
            'text-generation' => "请用自己的话重写这段文字。\n---\n文字:\n[[text]]\n\n---\n重写后的文字:",
            'text-generation-with-seo-keywords' => "请用自己的话重写这段文字。如果可能，请使用以下SEO关键字:[[keywords]]\n---\n文字:\n[[text]]\n\n---\n重写后的文字:",
            'image' => "描述与此文本最匹配的图像:\n[[text]]\n---\n用一句话创造性地描述图像:\n",
            'summary' => "用一句话总结以下文本:\n[[text]]\n---\n一句话总结:",
            'title' => "生成一篇关于以下主题的文章标题:\n[[summaries]]\n---\n标题:",
            'title-with-seo-keywords' => "生成一篇关于以下文本的文章标题。如果可能，请使用以下SEO关键字:[[keywords]]\n---\n文本:\n[[summaries]]\n---\n标题:",
        ],
    ],
    'br' => [
        'prompts' => [
            'text-generation' => "Reescreva este texto com suas próprias palavras.\n---\nTexto:\n[[text]]\n\n---\nTexto reescrito:",
            'text-generation-with-seo-keywords' => "Reescreva este texto com suas próprias palavras e tente usar as seguintes palavras-chave de SEO, se possível:[[keywords]]\n---\nTexto:\n[[text]]\n\n---\nTexto reescrito:",
            'image' => "Descreva a imagem que melhor se adapta a este texto:\n[[text]]\n---\nDescrição criativa da imagem em uma frase:\n",
            'summary' => "Escreva um resumo do seguinte texto em uma frase:\n[[text]]\n---\nResumo em uma frase:",
            'title' => "Gere um título para um artigo sobre os seguintes tópicos:\n[[summaries]]\n---\nTítulo:",
            'title-with-seo-keywords' => "Gere um título para um artigo sobre o seguinte texto e tente usar as seguintes palavras-chave de SEO, se possível:[[keywords]]\n---\nTexto:\n[[summaries]]\n---\nTítulo:",
        ],
    ],
    'tr' => [
        'prompts' => [
            'text-generation' => "Bu metni kendi sözlerinizle yeniden yazın.\n---\nMetin:\n[[text]]\n\n---\nYeniden yazılmış metin:",
            'text-generation-with-seo-keywords' => "Bu metni kendi sözlerinizle yeniden yazın ve mümkünse aşağıdaki SEO anahtar kelimelerini kullanmaya çalışın:[[keywords]]\n---\nMetin:\n[[text]]\n\n---\nYeniden yazılmış metin:",
            'image' => "Bu metne en uygun görseli açıklayın:\n[[text]]\n---\nResmin yaratıcı bir açıklaması 6 kelimeyle:\n",
            'summary' => "Aşağıdaki metnin özetini bir cümlede yazın:\n[[text]]\n---\nBir cümlede özet:",
            'title' => "Aşağıdaki konularla ilgili bir makale başlığı oluşturun:\n[[summaries]]\n---\nBaşlık:",
            'title-with-seo-keywords' => "Aşağıdaki metinle ilgili bir makale başlığı oluşturun ve mümkünse aşağıdaki SEO anahtar kelimelerini kullanmaya çalışın:[[keywords]]\n---\nMetin:\n[[summaries]]\n---\nBaşlık:",
        ],
    ],
    'ar' => [
        'prompts' => [
            'text-generation' => "أعد صياغة هذا النص بكلماتك الخاصة.\n---\nالنص:\n[[text]]\n\n---\nالنص المعاد صياغته:",
            'text-generation-with-seo-keywords' => "أعد صياغة هذا النص بكلماتك الخاصة وحاول استخدام الكلمات الرئيسية التالية لتحسين محركات البحث إذا كان ذلك ممكنًا:[[keywords]]\n---\nالنص:\n[[text]]\n\n---\nالنص المعاد صياغته:",
            'image' => "صف الصورة التي تتناسب أكثر مع هذا النص:\n[[text]]\n---\nوصف إبداعي للصورة في جملة واحدة:\n",
            'summary' => "اكتب ملخصًا للنص التالي في جملة واحدة:\n[[text]]\n---\nملخص في جملة واحدة:",
            'title' => "أنشئ عنوانًا لمقالة حول المواضيع التالية:\n[[summaries]]\n---\nالعنوان:",
            'title-with-seo-keywords' => "أنشئ عنوانًا لمقالة حول النص التالي وحاول استخدام الكلمات الرئيسية التالية لتحسين محركات البحث إذا كان ذلك ممكنًا:[[keywords]]\n---\nالنص:\n[[summaries]]\n---\nالعنوان:",
        ],
    ],
    'ko' => [
        'prompts' => [
            'text-generation' => "이 텍스트를 당신의 말로 다시 써보세요.\n---\n텍스트:\n[[text]]\n\n---\n다시 쓴 텍스트:",
            'text-generation-with-seo-keywords' => "이 텍스트를 당신의 말로 다시 써보세요. 가능하다면 다음 SEO 키워드를 사용해보세요:[[keywords]]\n---\n텍스트:\n[[text]]\n\n---\n다시 쓴 텍스트:",
            'image' => "이 텍스트에 가장 적합한 이미지를 설명해보세요:\n[[text]]\n---\n이미지에 대한 창의적인 설명 한 문장:",
            'summary' => "다음 텍스트의 요약을 한 문장으로 써보세요:\n[[text]]\n---\n한 문장으로 요약:",
            'title' => "다음 주제에 대한 기사 제목을 만들어보세요:\n[[summaries]]\n---\n제목:",
            'title-with-seo-keywords' => "다음 텍스트에 대한 기사 제목을 만들어보세요. 가능하다면 다음 SEO 키워드를 사용해보세요:[[keywords]]\n---\n텍스트:\n[[summaries]]\n---\n제목:",
        ],
    ],

    'hi' => [
        'prompts' => [
            'text-generation' => "इस पाठ को अपने शब्दों में फिर से लिखें।\n---\nपाठ:\n[[text]]\n\n---\nफिर से लिखा पाठ:",
            'text-generation-with-seo-keywords' => "इस पाठ को अपने शब्दों में फिर से लिखें और यदि संभव हो तो निम्नलिखित एसईओ कीवर्ड का उपयोग करने का प्रयास करें:[[keywords]]\n---\nपाठ:\n[[text]]\n\n---\nफिर से लिखा पाठ:",
            'image' => "इस पाठ के लिए सबसे उपयुक्त चित्र का वर्णन करें:\n[[text]]\n---\nचित्र के लिए एक रचनात्मक वर्णन एक वाक्य में:",
            'summary' => "निम्नलिखित पाठ का सारांश एक वाक्य में लिखें:\n[[text]]\n---\nएक वाक्य में सारांश:",
            'title' => "निम्नलिखित विषयों पर एक लेख का शीर्षक बनाएं:\n[[summaries]]\n---\nशीर्षक:",
            'title-with-seo-keywords' => "निम्नलिखित पाठ पर एक लेख का शीर्षक बनाएं और यदि संभव हो तो निम्नलिखित एसईओ कीवर्ड का उपयोग करें:[[keywords]]\n---\nपाठ:\n[[summaries]]\n---\nशीर्षक:",
        ],
    ],
    'id' => [
        'prompts' => [
            'text-generation' => "Tulis ulang teks ini dengan kata-kata Anda sendiri.\n---\nTeks:\n[[text]]\n\n---\nTeks yang ditulis ulang:",
            'text-generation-with-seo-keywords' => "Tulis ulang teks ini dengan kata-kata Anda sendiri dan cobalah untuk menggunakan kata kunci SEO berikut jika memungkinkan:[[keywords]]\n---\nTeks:\n[[text]]\n\n---\nTeks yang ditulis ulang:",
            'image' => "Deskripsikan gambar yang paling cocok untuk teks ini:\n[[text]]\n---\nDeskripsi kreatif satu kalimat untuk gambar:",
            'summary' => "Tulis ringkasan satu kalimat untuk teks berikut:\n[[text]]\n---\nRingkasan satu kalimat:",
            'title' => "Buat judul artikel untuk topik berikut:\n[[summaries]]\n---\nJudul:",
            'title-with-seo-keywords' => "Buat judul artikel untuk teks berikut dan cobalah untuk menggunakan kata kunci SEO berikut jika memungkinkan:[[keywords]]\n---\nTeks:\n[[summaries]]\n---\nJudul:",

        ]
    ],
    'sv' => [
        'prompts' => [
            'text-generation' => "Skriv om denna text med dina egna ord.\n---\nText:\n[[text]]\n\n---\nOmskriven text:",
            'text-generation-with-seo-keywords' => "Skriv om denna text med dina egna ord och försök att använda följande SEO-nyckelord om möjligt:[[keywords]]\n---\nText:\n[[text]]\n\n---\nOmskriven text:",
            'image' => "Beskriv den mest passande bilden för denna text:\n[[text]]\n---\nKreativ beskrivning av bilden i en mening:",
            'summary' => "Skriv en sammanfattning av följande text i en mening:\n[[text]]\n---\nSammanfattning i en mening:",
            'title' => "Skapa en artikelrubrik för följande ämnen:\n[[summaries]]\n---\nRubrik:",
            'title-with-seo-keywords' => "Skapa en artikelrubrik för följande text och försök att använda följande SEO-nyckelord om möjligt:[[keywords]]\n---\nText:\n[[summaries]]\n---\nRubrik:",

        ]
    ],
    'da' => [
        'prompts' => [
            'text-generation' => "Skriv om denne tekst med dine egne ord.\n---\nTekst:\n[[text]]\n\n---\nOmskrevet tekst:",
            'text-generation-with-seo-keywords' => "Skriv om denne tekst med dine egne ord og prøv at bruge følgende SEO-nøgleord, hvis det er muligt:[[keywords]]\n---\nTekst:\n[[text]]\n\n---\nOmskrevet tekst:",
            'image' => "Beskriv det mest passende billede til denne tekst:\n[[text]]\n---\nKreativ beskrivelse af billedet i en sætning:",
            'summary' => "Skriv en sammenfatning af følgende tekst i en sætning:\n[[text]]\n---\nSammenfatning i en sætning:",
            'title' => "Opret en artikeloverskrift for følgende emner:\n[[summaries]]\n---\nOverskrift:",
            'title-with-seo-keywords' => "Opret en artikeloverskrift for følgende tekst og prøv at bruge følgende SEO-nøgleord, hvis det er muligt:[[keywords]]\n---\nTekst:\n[[summaries]]\n---\nOverskrift:",
        ],
    ],
    'fi' => [
        'prompts' => [
            'text-generation' => "Kirjoita tämä teksti uudelleen omilla sanoillasi.\n---\nTeksti:\n[[text]]\n\n---\nUudelleenkirjoitettu teksti:",
            'text-generation-with-seo-keywords' => "Kirjoita tämä teksti uudelleen omilla sanoillasi ja yritä käyttää seuraavia SEO-avainsanoja, jos mahdollista:[[keywords]]\n---\nTeksti:\n[[text]]\n\n---\nUudelleenkirjoitettu teksti:",
            'image' => "Kuvaile sopivin kuva tähän tekstiin:\n[[text]]\n---\nLuova kuvaus kuvasta yhdellä lauseella:",
            'summary' => "Kirjoita yhteenveto seuraavasta tekstistä yhdellä lauseella:\n[[text]]\n---\nYhteenveto yhdellä lauseella:",
            'title' => "Luo artikkelin otsikko seuraaville aiheille:\n[[summaries]]\n---\nOtsikko:",
            'title-with-seo-keywords' => "Luo artikkelin otsikko seuraavalle tekstille ja yritä käyttää seuraavia SEO-avainsanoja, jos mahdollista:[[keywords]]\n---\nTeksti:\n[[summaries]]\n---\nOtsikko:",
        ]
    ],
    'no' => [
        'prompts' => [
            'text-generation' => "Skriv om denne teksten med dine egne ord.\n---\nTekst:\n[[text]]\n\n---\nOmskrevet tekst:",
            'text-generation-with-seo-keywords' => "Skriv om denne teksten med dine egne ord og prøv å bruke følgende SEO-nøkkelord hvis mulig:[[keywords]]\n---\nTekst:\n[[text]]\n\n---\nOmskrevet tekst:",
            'image' => "Beskriv det mest passende bildet for denne teksten:\n[[text]]\n---\nKreativ beskrivelse av bildet i en setning:",
            'summary' => "Skriv en sammendrag av følgende tekst i en setning:\n[[text]]\n---\nSammendrag i en setning:",
            'title' => "Lag en artikkeloverskrift for følgende emner:\n[[summaries]]\n---\nOverskrift:",
            'title-with-seo-keywords' => "Lag en artikkeloverskrift for følgende tekst og prøv å bruke følgende SEO-nøkkelord hvis mulig:[[keywords]]\n---\nTekst:\n[[summaries]]\n---\nOverskrift:",

        ],
    ],
    'ro' => [
        'prompts' => [
            'text-generation' => "Rescrie acest text cu propriile cuvinte.\n---\nText:\n[[text]]\n\n---\nText rescris:",
            'text-generation-with-seo-keywords' => "Rescrie acest text cu propriile cuvinte și încearcă să folosești următoarele cuvinte cheie SEO, dacă este posibil:[[keywords]]\n---\nText:\n[[text]]\n\n---\nText rescris:",
            'image' => "Descrie imaginea cea mai potrivită pentru acest text:\n[[text]]\n---\nDescriere creativă a imaginii într-o singură propoziție:",
            'summary' => "Scrie un rezumat al următorului text într-o singură propoziție:\n[[text]]\n---\nRezumat într-o singură propoziție:",
            'title' => "Creează un titlu de articol pentru următoarele subiecte:\n[[summaries]]\n---\nTitlu:",
            'title-with-seo-keywords' => "Creează un titlu de articol pentru următorul text și încearcă să folosești următoarele cuvinte cheie SEO, dacă este posibil:[[keywords]]\n---\nText:\n[[summaries]]\n---\nTitlu:",
        ],
    ],
    'ka' => [
        'prompts' => [
            'text-generation' => "გადაწერეთ ეს ტექსტი თქვენს საკუთარ სიტყვებით.\n---\nტექსტი:\n[[text]]\n\n---\nგადაწერილი ტექსტი:",
            'text-generation-with-seo-keywords' => "გადაწერეთ ეს ტექსტი თქვენს საკუთარ სიტყვებით და სცადეთ შემდეგი SEO საკვანძო სიტყვების გამოყენება, თუ შესაძლებელია:[[keywords]]\n---\nტექსტი:\n[[text]]\n\n---\nგადაწერილი ტექსტი:",
            'image' => "აღწერეთ ეს ტექსტის უახლოესი სურათი:\n[[text]]\n---\nსურათის შემადგენლობა ერთ წინადადებაში:",
            'summary' => "დაწერეთ შემდეგი ტექსტის რეზიუმე ერთ წინადადებაში:\n[[text]]\n---\nრეზიუმე ერთ წინადადებაში:",
            'title' => "შექმენით სტატიის სათაური შემდეგი თემებისთვის:\n[[summaries]]\n---\nსათაური:",
            'title-with-seo-keywords' => "შექმენით სტატიის სათაური შემდეგი ტექსტისთვის და სცადეთ შემდეგი SEO საკვანძო სიტყვების გამოყენება, თუ შესაძლებელია:[[keywords]]\n---\nტექსტი:\n[[summaries]]\n---\nსათაური:",
        ],
    ],
    'vi' => [
        'prompts' => [
            'text-generation' => "Viết lại đoạn văn bản này bằng cách sử dụng từ của bạn.\n---\nVăn bản:\n[[text]]\n\n---\nVăn bản đã viết lại:",
            'text-generation-with-seo-keywords' => "Viết lại đoạn văn bản này bằng cách sử dụng từ của bạn và cố gắng sử dụng các từ khóa SEO sau nếu có thể:[[keywords]]\n---\nVăn bản:\n[[text]]\n\n---\nVăn bản đã viết lại:",
            'image' => "Mô tả hình ảnh sau bằng văn bản của bạn:\n[[text]]\n---\nMô tả hình ảnh trong một câu:",
            'summary' => "Viết một đoạn văn bản ngắn mô tả đoạn văn bản sau:\n[[text]]\n---\nMô tả trong một câu:",
            'title' => "Tạo tiêu đề cho bài viết sau:\n[[summaries]]\n---\nTiêu đề:",
            'title-with-seo-keywords' => "Tạo tiêu đề cho bài viết sau và cố gắng sử dụng các từ khóa SEO sau nếu có thể:[[keywords]]\n---\nTiêu đề:",

        ],
    ],
    'hu' => [
        'prompts' => [
            'text-generation' => "Írja át ezt a szöveget a saját szavai használatával.\n---\nSzöveg:\n[[text]]\n\n---\nÁtírt szöveg:",
            'text-generation-with-seo-keywords' => "Írja át ezt a szöveget a saját szavai használatával, és próbálja meg használni a következő SEO kulcsszavakat, ha lehetséges:[[keywords]]\n---\nSzöveg:\n[[text]]\n\n---\nÁtírt szöveg:",
            'image' => "Jellemezze a következő képet a saját szavai használatával:\n[[text]]\n---\nKép leírása egy mondatban:",
            'summary' => "Írjon egy rövid szöveget, amely leírja a következő szöveget:\n[[text]]\n---\nLeírás egy mondatban:",
            'title' => "Hozzon létre egy címet a következő szöveghez:\n[[summaries]]\n---\nCím:",
            'title-with-seo-keywords' => "Hozzon létre egy címet a következő szöveghez, és próbálja meg használni a következő SEO kulcsszavakat, ha lehetséges:[[keywords]]\n---\nCím:",

        ],
    ],
    'bg' => [
        'prompts' => [
            'text-generation' => "Пренапишете този текст, като използвате собствените си думи.\n---\nТекст:\n[[text]]\n\n---\nПренаписан текст:",
            'text-generation-with-seo-keywords' => "Пренапишете този текст, като използвате собствените си думи и се опитайте да използвате следните SEO ключови думи, ако е възможно:[[keywords]]\n---\nТекст:\n[[text]]\n\n---\nПренаписан текст:",
            'image' => "Опишете следната снимка със собствените си думи:\n[[text]]\n---\nОписание на изображението в едно изречение:",
            'summary' => "Напишете кратък текст, който описва следния текст:\n[[text]]\n---\nОписание в едно изречение:",
            'title' => "Създайте заглавие за следния текст:\n[[summaries]]\n---\nЗаглавие:",
            'title-with-seo-keywords' => "Създайте заглавие за следния текст и се опитайте да използвате следните SEO ключови думи, ако е възможно:[[keywords]]\n---\nЗаглавие:",

        ],
    ],
    'el' => [
        'prompts' => [
            'text-generation' => "Επαναδιατυπώστε αυτό το κείμενο χρησιμοποιώντας τις δικές σας λέξεις.\n---\nΚείμενο:\n[[text]]\n\n---\nΕπαναδιατυπωμένο κείμενο:",
            'text-generation-with-seo-keywords' => "Επαναδιατυπώστε αυτό το κείμενο χρησιμοποιώντας τις δικές σας λέξεις και προσπαθήστε να χρησιμοποιήσετε τις ακόλουθες λέξεις-κλειδιά SEO, εάν είναι δυνατόν:[[keywords]]\n---\nΚείμενο:\n[[text]]\n\n---\nΕπαναδιατυπωμένο κείμενο:",
            'image' => "Περιγράψτε την ακόλουθη εικόνα με τις δικές σας λέξεις:\n[[text]]\n---\nΠεριγραφή εικόνας σε μια πρόταση:",
            'summary' => "Γράψτε ένα σύντομο κείμενο που περιγράφει το ακόλουθο κείμενο:\n[[text]]\n---\nΠεριγραφή σε μια πρόταση:",
            'title' => "Δημιουργήστε έναν τίτλο για το ακόλουθο κείμενο:\n[[summaries]]\n---\nΤίτλος:",
            'title-with-seo-keywords' => "Δημιουργήστε έναν τίτλο για το ακόλουθο κείμενο και προσπαθήστε να χρησιμοποιήστε τις ακόλουθες λέξεις-κλειδιά SEO, εάν είναι δυνατόν:[[keywords]]\n---\nΤίτλος:",

        ],
    ],
    'fa' => [
        'prompts' => [
            'text-generation' => "این متن را با استفاده از کلمات خودتان مجدداً بنویسید.\n---\nمتن:\n[[text]]\n\n---\nمتن مجدداً نوشته شده:",
            'text-generation-with-seo-keywords' => "این متن را با استفاده از کلمات خودتان مجدداً بنویسید و سعی کنید از کلمات کلیدی SEO زیر استفاده کنید، اگر امکان دارد:[[keywords]]\n---\nمتن:\n[[text]]\n\n---\nمتن مجدداً نوشته شده:",
            'image' => "تصویر زیر را با استفاده از کلمات خودتان توصیف کنید:\n[[text]]\n---\nتوصیف تصویر در یک جمله:",
            'summary' => "یک متن کوتاه بنویسید که متن زیر را توصیف می کند:\n[[text]]\n---\nتوصیف در یک جمله:",
            'title' => "برای متن زیر یک عنوان ایجاد کنید:\n[[summaries]]\n---\nعنوان:",
            'title-with-seo-keywords' => "برای متن زیر یک عنوان ایجاد کنید و سعی کنید از کلمات کلیدی SEO زیر استفاده کنید، اگر امکان دارد:[[keywords]]\n---\nعنوان:",

        ],
    ],
    'sk' => [
        'prompts' => [
            'text-generation' => "Tento text napíšte znova pomocou vlastných slov.\n---\nText:\n[[text]]\n\n---\nOpísaný text:",
            'text-generation-with-seo-keywords' => "Tento text napíšte znova pomocou vlastných slov a skúste použiť tieto SEO kľúčové slová, ak je to možné:[[keywords]]\n---\nText:\n[[text]]\n\n---\nOpísaný text:",
            'image' => "Popíšte nasledujúci obrázok pomocou vlastných slov:\n[[text]]\n---\nPopis obrázka v jednej vete:",
            'summary' => "Napíšte krátky text, ktorý opisuje nasledujúci text:\n[[text]]\n---\nPopis v jednej vete:",
            'title' => "Vytvorte názov pre nasledujúci text:\n[[summaries]]\n---\nNázov:",
            'title-with-seo-keywords' => "Vytvorte názov pre nasledujúci text a skúste použiť tieto SEO kľúčové slová, ak je to možné:[[keywords]]\n---\nNázov:",

        ],
    ],
    'cs' => [
        'prompts' => [
            'text-generation' => "Tento text napište znovu pomocí vlastních slov.\n---\nText:\n[[text]]\n\n---\nOpisovaný text:",
            'text-generation-with-seo-keywords' => "Tento text napište znovu pomocí vlastních slov a zkuste použít tato SEO klíčová slova, pokud je to možné:[[keywords]]\n---\nText:\n[[text]]\n\n---\nOpisovaný text:",
            'image' => "Popište následující obrázek pomocí vlastních slov:\n[[text]]\n---\nPopis obrázku v jedné větě:",
            'summary' => "Napište krátký text, který popisuje následující text:\n[[text]]\n---\nPopis v jedné větě:",
            'title' => "Vytvořte název pro následující text:\n[[summaries]]\n---\nNázev:",
            'title-with-seo-keywords' => "Vytvořte název pro následující text a zkuste použít tato SEO klíčová slova, pokud je to možné:[[keywords]]\n---\nNázev:",
        ],
    ],
    'ca' => [
        'prompts' => [
            'text-generation' => "Reescriu cada part d'aquest text amb les teves pròpies paraules.\n---\nText:\n[[text]]\n\n---\nText reescrit:",
            'text-generation-with-seo-keywords' => "Reescriu cada part d'aquest text amb les teves pròpies paraules i intenta utilitzar les següents paraules clau SEO quan sigui possible: [[keywords]]\n---\nText:\n[[text]]\n\n---\nText reescrit:",
            'image' => "Descriu una imatge que sigui la millor per a aquest text:\n[[text]]\n---\nDescripció creativa de la imatge en una frase de 6 paraules:\n",
            'summary' => "Escriu un resum del següent text en una frase:\n[[text]]\n---\nResum en una frase:",
            'title' => "Genera un títol per a un article que tracta els següents temes:\n[[summaries]]\n---\nTítol:",
            'title-with-seo-keywords' => "Genera un títol per a un article que tracta el següent text i intenta utilitzar les següents paraules clau SEO quan sigui possible:[[keywords]]\n---\nText:\n[[summaries]]\n---\nTítol:",
        ]
    ],
    'hr' => [
        'prompts' => [
            'text-generation' => "Prepišite svaki dio ovog teksta svojim riječima.\n---\nTekst:\n[[text]]\n\n---\nPrepisani tekst:",
            'text-generation-with-seo-keywords' => "Prepišite svaki dio ovog teksta svojim riječima i pokušajte koristiti sljedeće SEO ključne riječi kada je to moguće: [[keywords]]\n---\nTekst:\n[[text]]\n\n---\nPrepisani tekst:",
            'image' => "Opišite sliku koja bi najbolje odgovarala ovom tekstu:\n[[text]]\n---\nKreativan opis slike u jednoj rečenici od 6 riječi:\n",
            'summary' => "Napišite sažetak sljedećeg teksta u jednoj rečenici:\n[[text]]\n---\nSažetak u jednoj rečenici:",
            'title' => "Generirajte naslov članka koji raspravlja o sljedećim temama:\n[[summaries]]\n---\nNaslov:",
            'title-with-seo-keywords' => "Generirajte naslov članka koji raspravlja o sljedećem tekstu i pokušajte koristiti sljedeće SEO ključne riječi kada je to moguće:[[keywords]]\n---\nTekst:\n[[summaries]]\n---\nNaslov:",
        ]
    ],
    'uk' => [
        'prompts' => [
            'text-generation' => "Перепишіть цей текст своїми словами.\n---\nТекст:\n[[text]]\n\n---\nПереписаний текст:",
            'text-generation-with-seo-keywords' => "Перепишіть цей текст своїми словами та намагайтеся використовувати наступні ключові слова SEO, коли це можливо: [[keywords]]\n---\nТекст:\n[[text]]\n\n---\nПереписаний текст:",
            'image' => "Опишіть зображення, яке найкраще підходить для цього тексту:\n[[text]]\n---\nКреативний опис зображення в одному реченні з 6 словами:\n",
            'summary' => "Напишіть резюме наступного тексту в одному реченні:\n[[text]]\n---\nРезюме в одному реченні:",
            'title' => "Згенеруйте заголовок статті, яка обговорює наступні теми:\n[[summaries]]\n---\nЗаголовок:",
            'title-with-seo-keywords' => "Згенеруйте заголовок статті, яка обговорює наступний текст та намагайтеся використовувати наступні ключові слова SEO, коли це можливо:[[keywords]]\n---\nТекст:\n[[summaries]]\n---\nЗаголовок:",
        ]
    ],
];
