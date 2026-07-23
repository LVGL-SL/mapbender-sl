


var materialII = function(i)
{
this.matarray = new Array(i);
for(var x=0;x<i;x++)
this.matarray[x] = new material();
this.matanzahl=0;
};

materialII.prototype.addmat = function(mat)
{

this.matarray[this.matanzahl].name = mat.name;
this.matarray[this.matanzahl].ns = mat.ns;
this.matarray[this.matanzahl].ka[0] = mat.ka[0];
this.matarray[this.matanzahl].ka[1] = mat.ka[1];
this.matarray[this.matanzahl].ka[2] = mat.ka[2];
this.matarray[this.matanzahl].kd[0] = mat.kd[0];
this.matarray[this.matanzahl].kd[1] = mat.kd[1];
this.matarray[this.matanzahl].kd[2] = mat.kd[2];
this.matarray[this.matanzahl].ks[0] = mat.ks[0];
this.matarray[this.matanzahl].ks[1] = mat.ks[1];
this.matarray[this.matanzahl].ks[2] = mat.ks[2];
this.matarray[this.matanzahl].illum = mat.illum;
this.matarray[this.matanzahl].d = mat.d;
this.matarray[this.matanzahl].s = mat.s;

this.matanzahl++;

}

materialII.prototype.getmat = function(i)
{
  return this.matarray[i];
}
