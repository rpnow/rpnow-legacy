var RPRandom = (function() {
  
  var dictionary = {
    Title: [
      ":The :Noun",
      ":The :Nouns",
      ":My :Noun",
      ":My :Nouns",
      ":The :Adjective :Noun",
      ":The :Adjective :Nouns",
      ":My :Adjective :Noun",
      ":My :Adjective :Nouns",
      ":A :Noun :Prep :Nouns",
      ":Prep :the :Noun",
      ":The :Adjective",
      ":The :Adjective :Noun",
      ":Verbed",
      ":Verbed by :the :Noun",
      ":Verbed by :the :Nouns",
      
    ],
    the: [
      "the", ""
    ],
    The: [
      "The", ""
    ],
    A: [
      "A", ""
    ],
    My: [
      "My", "Our", "Your", "His", "Her", "Their"
    ],
    Noun: [
      "Anchor", "Anything", "Autumn",
      "Bone", "Book", "Blade", "Boy", "Breeze", "Bullet",
      "Dog",
      "Elephant", "Everything",
      "Carnival",
      "Event",
      "Fire", "Flame", "Forest",
      "Gate", "Girl",
      "Haze", "Hint",
      "Land", "Letter", "Light", "Lord", "Luck",
      "Man",
      "Nothing",
      "Page", "Power", "Pulse",
      "Ring", "Rock",
      "Something", "Someone", "Sound", "Soul", "Stone", "Stranger", 
      "Wanderer", "Winter", "Word", "Wrath",
      "Zoo",
      ":Noun :Noun"
    ],
    Nouns: [
      ":Noun:s"
    ],
    Prep: [
      "Above", "Across", "Along", "Among", "Around",
      "Before", "Below", "Between", "Betwixt", "Beyond",
      "From"
    ],
    Adjective: [
      "Auburn",
      "Blue", "Breezy",
      "Colored", "Cold", "Curious",
      "Green",
      "Hidden",
      "Littlest", "Lost",
      "Peculiar", "Periwinkle",
      "Smallest",
      "Unfortunate", "Unmarked", "Unbreakable",
      "Wandering", "Wonderful",
    ],
    Verbed: [
      "Consumed",
      "Encompassed",
      "Forgotten",
      "Kept",
      "Lost",
      "Revered"
    ]
  };
  
  function generateTitle() {
    // start with some title
    var str = ":Title";
    
    // resolve all terms
    do {
      var lastStr = str;
      str = str.replace(/:([a-zA-Z]+):?/, function(match, inner) {
        var x = dictionary[inner];
        if(x)
          return x[Math.floor(Math.random()*x.length)];
        else
          return match.toUpperCase() + '?';
      });
    } while(str !== lastStr);
    
    // remove extra spaces and return
    return str.trim().replace(/\s+/g, ' ');
  }

  function generateShortTitle(maxLength) {
    var str;
    do {
      str = generateTitle();
    } while(str.length > maxLength);
    return str;
  }
  
  return {
    title: generateTitle,
    shortTitle: generateShortTitle
  };
  
})();