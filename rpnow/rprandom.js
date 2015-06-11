var RPRandom = (function() {

  var dictionary = {
    Title: [
      ":The :Nounss",
      ":The :Noun of :the :Nounss",
      ":The :Adjective",
      ":The :Adjective :Nounss",
      ":The :Adjective :Adjective :Nounss",
      ":The :Noun's :Nounss",
      ":The :Noun's :Adjective :Nounss",
      ":My :Nounss",
      ":My :Adjective :Nounss",
      ":The :Nounss :Prep :Nounss",
      ":A :Noun :Prep :Nounss",
      ":Prep :the :Nounss",
      ":Prep :the :Adjective :Nounss",
      ":Prep :My :Nounss",
      ":Prep :My :Adjective :Nounss",
      ":Verbed",
      ":Verbed by :the :Nounss",
      ":I :Verbed",
      ":Who :I :Verbed",
      ":IAm :Adjective",
      ":IAm :the :Noun",
      ":Who :IAm",
      ":Who :IAm :Prep :the :Nounss",
      ":Who :IAm :Prep :Verb:ing",
      ":Who :IAm :Prep :Me"
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
    I: [
      "I", "You", "He", "She", "We", "They", "It"
    ],
    IAm: [
      "I Am", "You Are", "He Is", "She Is", "We Are", "They Are", "It Was"
    ],
    Me: [
      "Me", "You", "Him", "Her", "Us", "Them", "It"
    ],
    My: [
      "My", "Our", "Your", "His", "Her", "Their"
    ],
    Noun: [
      "Air", "Altar", "Amber", "Anchor", "Animal", "Anything", "Apology", "Autumn",
      "Banana", "Bat", "Bean", "Bed", "Bell", "Bet", "Bit", "Blade", "Bone", "Book", "Box", "Boy", "Bread", "Breeze", "Bullet",
      "Call", "Case", "Cat", "Carnival", "Chasm", "Circle", "City", "Contraption", "Clock", "Cloud", "Core", "Creation", "Cure",
      "Danger", "Dawn", "Dog",
      "Earth", "Echo", "Egg", "Elephant", "Event", "Era", "Everything",
      "Farm", "Fish", "Fire", "Field", "Flame", "Forest", "Friend",
      "Game", "Gate", "Ghost", "Girl",
      "Hand", "Haze", "Heart", "Hint", "Horizon",
      "Invention", "Inventor",
      "King", "Knife",
      "Land", "Laughter", "Letter", "Light", "Lord", "Love", "Luck",
      "Man", "Map", "Material", "Maze", "Memory", "Mind", "Mirror", "Mountain",
      "Note", "Nothing",
      "Ocean",
      "Page", "Pasture", "Penumbra", "Pilot", "Pioneer", "Plain", "Plane", "Pond", "Power", "Pulse",
      "Queen",
      "Reflection", "Ring", "Ridge", "Rock",
      "Sailboat", "Seed", "Shade", "Shadow", "Ship", "Skull", "Sky", "Smile", "Something", "Someone", "Sound", "Soul", "Spider", "Spike", "Spoon", "Spring", "Stair", "Star", "Stone", "Stranger", "String", "Sugar",
      "Tear", "Throne", "Time", "Touch",
      "Ultimatum", "Umbrella",
      "Vinegar", "Void", "Voyage",
      "Wand", "Wanderer", "Water", "Wedding", "Winter", "Wolf", "Word", "Wrath", "Wrinkle",
      "Zone", "Zoo"
    ],
    Nouns: [
      ":Noun:s"
    ],
    Nounss: [
      ":Noun", ":Nouns"
    ],
    Prep: [
      "Above", "Across", "After", "Along", "Among", "Around",
      "Before", "Behind", "Below", "Beneath", "Between", "Betwixt", "Beyond",
      "From",
      "Into", "Inside",
      "Over",
      "Through",
      "Under", "Until", "Upon",
      "Without"
    ],
    Who: [
      "Who", "What", "When", "Where", "Why", "How", "Which"
    ],
    Adjective: [
      "Auburn", "Azure",
      "Beloved", "Blue", "Blonde", "Breezy",
      "Colored", "Cold", "Curious",
      "Dark", "Deep",
      "Enormous",
      "Fallen",
      "Green",
      "Haunted", "Hectic", "Hidden", "Hungry",
      "Littlest", "Light", "Lost",
      "Old",
      "Peculiar", "Periwinkle",
      "Quiet",
      "Shady", "Silly", "Smallest", "Subtle",
      "Tattered", "Torn", "Tricky", "True",
      "Unfortunate", "Unknown", "Unmarked", "Unbreakable",
      "Violet",
      "Wandering", "Wonderful",
    ],
    Verb: [
      "Act", "Ascend", "Attack",
      "Bail",
      "Remember",
    ],
    Verbed: [
      "Acted", "Allowed",
      "Bought",
      "Cared", "Consumed", "Created", "Cut",
      "Doubted",
      "Encompassed", "Entrusted",
      "Forgot",
      "Kept",
      "Lost",
      "Missed",
      "Raced", "Ran", "Revered", "Remembered",
      "Stole",
    ]
  };
  
  // function for replacing individual terms in a string
  function dictRep(match, inner) {
    var x = dictionary[inner];
    if(x) return x[Math.floor(Math.random()*x.length)];
    else return inner.toUpperCase() + '?';
  }

  function resolve(str) {
    // resolve all terms
    do {
      var lastStr = str;
      str = str.replace(/:([a-zA-Z]+):?/, dictRep);
    } while(str !== lastStr);

    // remove extra spaces and return
    return str.trim().replace(/\s+/g, ' ');
  }

  function resolveShort(input, maxLength) {
    // keep resolving until it's short enough
    var str;
    do {
      str = resolve(input);
    } while(str.length > maxLength);
    return str;
  }

  return {
    title: function() {
      return resolve(":Title");
    },
    shortTitle: function(i) {
      return resolveShort(":Title", i);
    }
  };

})();