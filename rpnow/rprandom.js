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
      ":Prep :the :Nouns",
      ":The :Adjective",
      ":The :Adjective :Noun",
      ":Verbed",
      ":Verbed by :the :Noun",
      ":Verbed by :the :Nouns",
      ":Verbed :Noun",
      ":Verbed :Nouns",
      ":I :Verbed",
      ":Who :I :Verbed"
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
    I: [
      "I", "You", "He", "She", "We", "They", "It"
    ],
    Noun: [
      "Anchor", "Animal", "Anything", "Autumn",
      "Bone", "Book", "Blade", "Boy", "Breeze", "Bullet",
      "Case", "Carnival", "Chasm", "Circle", "Contraption", "Cloud",
      "Dog",
      "Elephant", "Event", "Era", "Everything",
      "Farm", "Fire", "Field", "Flame", "Forest", "Friend",
      "Gate", "Girl",
      "Hand", "Haze", "Heart", "Hint", "Horizon",
      "Invention", "Inventor",
      "Land", "Laughter", "Letter", "Light", "Lord", "Luck",
      "Man", "Material", "Mind",
      "Nothing",
      "Ocean",
      "Page", "Pasture", "Pilot", "Pioneer", "Plain", "Plane", "Power", "Pulse",
      "Queen",
      "Ring", "Ridge", "Rock",
      "Sailboat", "Shade", "Shadow", "Ship", "Smile", "Something", "Someone", "Sound", "Soul", "Stone", "Stranger",
      "Tear", "Time",
      "Umbrella", 
      "Wanderer", "Water", "Winter", "Word", "Wrath", "Wrinkle",
      "Zoo",
      ":Noun :Noun"
    ],
    Nouns: [
      ":Noun:s"
    ],
    Prep: [
      "Above", "Across", "Along", "Among", "Around",
      "Before", "Below", "Between", "Betwixt", "Beyond",
      "From",
      "Over",
      "Under",
      "Through",
    ],
    Who: [
      "Who", "What", "When", "Where", "Why", "How", "Which"
    ],
    Adjective: [
      "Auburn",
      "Blue", "Blonde", "Breezy",
      "Colored", "Cold", "Curious",
      "Dark",
      "Fallen",
      "Green",
      "Haunted", "Hidden",
      "Littlest", "Light", "Lost",
      "Peculiar", "Periwinkle",
      "Quiet",
      "Shady", "Silly", "Smallest",
      "Tattered", "Torn", "True",
      "Unfortunate", "Unknown", "Unmarked", "Unbreakable",
      "Wandering", "Wonderful",
    ],
    Verbed: [
      "Consumed",
      "Encompassed",
      "Forgot",
      "Kept",
      "Lost",
      "Revered", "Remembered",
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