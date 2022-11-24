-------------------- spu_comuna---------------------------------
CREATE PROCEDURE [dbo].[spu_comuna](@ve_operacion varchar(20), @ve_cod_comuna numeric,@ve_cod_ciudad numeric=NULL, @ve_nom_comuna varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into comuna (cod_comuna, cod_ciudad, nom_comuna)
		values (@ve_cod_comuna,@ve_cod_ciudad,@ve_nom_comuna)
	end 
	if (@ve_operacion='UPDATE') begin
		update comuna 
		set cod_ciudad = @ve_cod_ciudad ,nom_comuna = @ve_nom_comuna
	    where cod_comuna = @ve_cod_comuna
	end 
	else if (@ve_operacion='DELETE') begin
		delete comuna 
    	where cod_comuna = @ve_cod_comuna
	end 
END
go