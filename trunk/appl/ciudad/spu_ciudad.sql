-------------------- spu_ciudad---------------------------------
CREATE PROCEDURE [dbo].[spu_ciudad](@ve_operacion varchar(20), @ve_cod_ciudad numeric,@ve_cod_region numeric=NULL,@ve_cod_pais numeric=NULL, @ve_nom_ciudad varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into ciudad (cod_ciudad, cod_region, cod_pais, nom_ciudad)
		values (@ve_cod_ciudad,@ve_cod_region,@ve_cod_pais,@ve_nom_ciudad)
	end 
	if (@ve_operacion='UPDATE') begin
		update ciudad 
		set cod_ciudad = @ve_cod_ciudad , cod_region = @ve_cod_region , cod_pais = @ve_cod_pais ,nom_ciudad = @ve_nom_ciudad
		where cod_ciudad = @ve_cod_ciudad
	end
	else if (@ve_operacion='DELETE') begin
		delete ciudad 
		where cod_ciudad = @ve_cod_ciudad
	end	
END
go