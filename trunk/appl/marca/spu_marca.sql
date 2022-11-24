-------------------- spu_marca ---------------------------------
CREATE PROCEDURE [dbo].[spu_marca](@ve_operacion varchar(20), @ve_cod_marca numeric, @ve_nom_marca varchar(100)=NULL, @ve_orden numeric=NULL)
AS
BEGIN
	if (@ve_operacion='INSERT') begin
		insert into marca (nom_marca, orden)
		values (@ve_nom_marca, @ve_orden)
	end 
	if (@ve_operacion='UPDATE') begin
		update marca
		set nom_marca = @ve_nom_marca,
			orden = @ve_orden		
		where cod_marca = @ve_cod_marca
	end
	else if (@ve_operacion='DELETE') begin
		delete marca 
    	where cod_marca = @ve_cod_marca
	end
	
EXECUTE sp_orden_parametricas 'MARCA'
	
END
go