-------------------- spu_bodega ---------------------------------
CREATE PROCEDURE [dbo].[spu_bodega](@ve_operacion varchar(20), @ve_cod_bodega numeric, @ve_nom_bodega varchar(100) = null, @ve_cod_tipo_bodega numeric = null)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into bodega (nom_bodega, cod_tipo_bodega)
		values (@ve_nom_bodega, @ve_cod_tipo_bodega)
	end
	if (@ve_operacion='UPDATE') begin
		update bodega
		set nom_bodega = @ve_nom_bodega
			,cod_tipo_bodega = @ve_cod_tipo_bodega
	    where cod_bodega = @ve_cod_bodega
	end
	else if (@ve_operacion='UPDATE_EMPRESA') begin
		update EMPRESA
		set	 COD_BODEGA = @ve_cod_bodega
		where COD_EMPRESA = @ve_cod_tipo_bodega
		--en la variable @ve_cod_tipo_bodega  viene el cod_empresa para reocupar el mismo codigo spu_bodega VM		
	end
	else if (@ve_operacion='DELETE') begin
		delete bodega
	    where cod_bodega = @ve_cod_bodega
	end
END
go