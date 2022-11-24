-------------------- spu_region ---------------------------------
CREATE PROCEDURE [dbo].[spu_region](@ve_operacion varchar(20), @ve_cod_region numeric,@ve_cod_pais numeric=NULL, @ve_nom_region varchar(100)=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into region (cod_region,cod_pais, nom_region)
		values (@ve_cod_region,@ve_cod_pais,@ve_nom_region)
	end 
	if (@ve_operacion='UPDATE') begin
		update region 
		set cod_region = @ve_cod_region , cod_pais = @ve_cod_pais ,nom_region = @ve_nom_region
	    where cod_region = @ve_cod_region
	end
	else if (@ve_operacion='DELETE') begin
		delete region 
    	where cod_region = @ve_cod_region
	end
END
go