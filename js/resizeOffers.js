/*Функции, которые отрисовывают горячие предложения и новинки на главной странице при загрузке и/или изменении окна */

//window.onresize = "resizeOffers()"; 
//window.onload = "resizeOffers()";

  function hideOffers() {
  
    for (i = 0; i < 7; i++)
      {
        if ($('hotOfferText'+i) && $('hotOfferPict'+i))
        {
          Element.hide('hotOfferText'+i);
          Element.hide('hotOfferPict'+i);   
          Element.hide('hotOfferDivider'+i);   
        }        

        if ($('newOfferText'+i) && $('newOfferPict'+i))
        {
          Element.hide('newOfferText'+i);
          Element.hide('newOfferPict'+i);   
          Element.hide('newOfferDivider'+i);   
        }
       
    }

  }

  function resizeOffers() {

    ($('hotOffers')) ? width = $('hotOffers').offsetWidth : width = 0;
    var imageWidth = 160; //ну пристреляли так к картинкам 120px //160
    var blockWidth = 573; //размер блока по умолчанию 573px
    //alert('1'); 
    if (width <= blockWidth)
    {
      hideOffers();
    }
    if (width >= blockWidth)
    {     
      hideOffers();
      num = Math.floor((width - blockWidth) / imageWidth);
      for (i = 0; i < num; i++)
      {
        if ($('hotOfferText'+i))
        {
          Element.show('hotOfferText'+i);
          Element.show('hotOfferPict'+i);    
          Element.show('hotOfferDivider'+i); 
        }        
        if ($('newOfferText'+i))
        { 
          Element.show('newOfferText'+i);
          Element.show('newOfferPict'+i);
          Element.show('newOfferDivider'+i);
        }
        
      }
    }
  }

window.onresize = resizeOffers;
window.onload = resizeOffers;