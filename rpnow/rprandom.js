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
      "Air", "Altar", "Amber", "Anchor", "Animal", "Anything", "Apology", "Autumn",
      "Banana", "Bat", "Bean", "Bed", "Bell", "Bet", "Bit", "Blade", "Bone", "Book", "Box", "Boy", "Bread", "Breeze", "Bullet",
      "Case", "Carnival", "Chasm", "Circle", "City", "Contraption", "Clock", "Cloud",
      "Danger", "Dog",
      "Earth", "Echo", "Egg", "Elephant", "Event", "Era", "Everything",
      "Farm", "Fish", "Fire", "Field", "Flame", "Forest", "Friend",
      "Gate", "Girl",
      "Hand", "Haze", "Heart", "Hint", "Horizon",
      "Invention", "Inventor",
      "Knife",
      "Land", "Laughter", "Letter", "Light", "Lord", "Love", "Luck",
      "Man", "Material", "Memory", "Mind", "Mountain",
      "Note", "Nothing",
      "Ocean",
      "Page", "Pasture", "Penumbra", "Pilot", "Pioneer", "Plain", "Plane", "Power", "Pulse",
      "Queen",
      "Ring", "Ridge", "Rock",
      "Sailboat", "Seed", "Shade", "Shadow", "Ship", "Skull", "Sky", "Smile", "Something", "Someone", "Sound", "Soul", "Spider", "Spring", "Stair", "Star", "Stone", "Stranger", "String",
      "Tear", "Time",
      "Ultimatum", "Umbrella",
      "Wanderer", "Water", "Wedding", "Winter", "Wolf", "Word", "Wrath", "Wrinkle",
      "Zone", "Zoo",
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
      "Beloved", "Blue", "Blonde", "Breezy",
      "Colored", "Cold", "Curious",
      "Dark",
      "Fallen",
      "Green",
      "Haunted", "Hidden", "Hungry",
      "Littlest", "Light", "Lost",
      "Old",
      "Peculiar", "Periwinkle",
      "Quiet",
      "Shady", "Silly", "Smallest", "Subtle",
      "Tattered", "Torn", "True",
      "Unfortunate", "Unknown", "Unmarked", "Unbreakable",
      "Wandering", "Wonderful",
    ],
    Verbed: [
      "Bought",
      "Consumed", "Cut",
      "Encompassed",
      "Forgot",
      "Kept",
      "Lost",
      "Revered", "Remembered",
      "Stole",
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